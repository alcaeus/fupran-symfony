# Symfony Bundle for MongoDB

This bundle provides integration of the [MongoDB library](https://github.com/mongodb/mongo-php-library) into Symfony. It
is designed as a lightweight alternative to the [Doctrine MongoDB ODM](https://github.com/doctrine/mongodb-odm),
providing only an integration to interact with MongoDB without providing all features of an ODM.

## Installation

Install the bundle with composer:

```bash
composer require mongodb/symfony-bundle
```

Then enable the bundle by adding it to the `bundles.php` file:

```php
// config/bundles.php

<?php

return [
    // Other bundles...
    MongoDB\Bundle\MongoDBBundle::class => ['all' => true],
];
```

## Configuration

Configuration is done in the `config/packages/mongodb.yaml` file. To get started, you need to configure at least one
client:

```yaml
# config/packages/mongodb.yaml
mongodb:
  clients:
    - id: 'default'
      uri: 'mongodb://localhost:27017'
      # uriOptions and driverOptions are optional, defaulting to an empty array
      uriOptions:
      driverOptions:
```

The `id` is used to reference the client in the service container. The `uri` is the connection string to connect to. For 
security reasons, it is recommended to read this value from your local environment and referencing it through an
environment variable in the container:

```yaml
mongodb:
  clients:
    - id: 'default'
      uri: '%env(MONGODB_URI)%'
```

The `uriOptions` and `driverOptions` are passed directly to the underlying MongoDB driver. See the [documentation](https://www.php.net/manual/en/mongodb-driver-manager.construct.php)
for available options.

## Client Usage

For each client, a service is registered in the container with the `mongodb.client.{id}` name. Note that the MongoDB
driver only establishes a connection to MongoDB when it is actually needed, so a client can be injected into services
without causing network traffic. If you register a single client, the bundle automatically registers a service alias for
this client. With autowiring enabled, you can inject the client into your services like this:

```php
use MongoDB\Client;

class MyService
{
    public function __construct(
        private Client $client,
    ) {}
}
```

If you register multiple clients, you can autowire a specific client by using the `AutowireClient` attribute in your
service, referencing the client id assigned in the configuration:

```php
use MongoDB\Bundle\Attribute\AutowireClient;
use MongoDB\Client;

class MyService
{
    public function __construct(
       #[AutowireClient('myCluster')]
        private Client $client,
    ) {}
}
```

## Database and Collection Usage

The client service provides access to databases and collections. You can access a database by calling the `selectDatabase`
method, passing the database name and potential options:

```php
use MongoDB\Client;
use MongoDB\Database;

class MyService
{
    private Database $database;

    public function __construct(
        Client $client,
    ) {
        $this->database = $client->selectDatabase('myDatabase');
    }
}
```

An alternative to this is using the `AutowireDatabase` attribute, again referencing the client id and database name:

```php
use MongoDB\Bundle\Attribute\AutowireDatabase;
use MongoDB\Database;

class MyService
{
    public function __construct(
        #[AutowireDatabase('myCluster', 'myDatabase')]
        private Database $database,
    ) {}
}
```

To inject a collection, you can either call the `selectCollection` method on a `Client` or `Database` instance. For
convenience, the `AutowireCollection` attribute provides a quicker alternative:

```php
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;

class MyService
{
    public function __construct(
        #[AutowireCollection(
            clientId: 'myCluster',
            databaseName: 'myDatabase',
            collectionName: 'myCollection'
        )]
        private Collection $collection,
    ) {}
}
```

You can also omit the `collectionName` option if the property name matches the collection name:

```php
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;

class MyService
{
    public function __construct(
        #[AutowireCollection(
            clientId: 'myCluster',
            databaseName: 'myDatabase',
        )]
        private Collection $myCollection,
    ) {}
}
```

## Mapping Documents to PHP Objects

Note: this section is a work in progress. Expect frequent changes until a stable release is available.

### Using a Codec

Documents from a collection can be mapped to PHP objects using a `DocumentCodec`. Once you've created a `DocumentCodec`
class for your document, you can pass it in the `codec` option when autowiring a collection:

```php
use MongoDB\Bundle\Attribute\AutowireCollection;
use MongoDB\Collection;

class MyService
{
    public function __construct(
        #[AutowireCollection(
            clientId: 'myCluster',
            databaseName: 'myDatabase',
            options: ['codec' => new MyDocumentCodec()],
        )]
        private Collection $myCollection,
    ) {}
}
```

When you specify a `codec` option, the `Collection` instance will use the codec to decode raw BSON into PHP objects
instead of using the driver's default type map.

### Mapping Document Metadata

Note: the metadata system is highly experimental and not finalised. It only covers basic mapping.

If you want to avoid creating your own codec, you can use the `Document` attribute to start mapping document metadata
for a PHP class. The `Document` attribute takes no option and only serves as a marker to indicate that there are
document mappings available for a class. To map properties to fields, use the `Field` attribute:

```php
use MongoDB\BSON\ObjectId;
use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;

#[Document]
class MyDocument
{
    #[Field(name: '_id')]
    public readonly ObjectId $id;

    #[Field]
    public string $name;

    public function __construct(
        string $name,
    ) {
        $this->id = new ObjectId();
        $this->name = $name;
    }
}
```

The `Field` attribute takes an optional `name` option, which defaults to the property name. The `name` refers to the
name of the field in the database. Note that you have to use this name when querying the database, as mapping
information is not used when sending queries to the database.

You can also map methods as fields. In that case, the return value of the method will be stored in the database, but it
won't be used when loading data from the database. This can be helpful to cache data used in queries.

Embedded documents are mapped as fields. The `Field` mapping will automatically detect embedded documents based on the
property type and use the appropriate codec:

```php
use MongoDB\BSON\ObjectId;
use MongoDB\Bundle\Attribute\Document;
use MongoDB\Bundle\Attribute\Field;

#[Document]
class MyDocument
{
    #[Field(name: '_id')]
    public readonly ObjectId $id;

    #[Field]
    public string $name;
    
    #[Field]
    public ?Address $address = null;

    public function __construct(
        string $name,
    ) {
        $this->id = new ObjectId();
        $this->name = $name;
    }
}
```

The `Field` attribute also takes a `codec` parameter to specify a custom codec for the field. This can be used to define
your own logic for mapping objects to a field.
