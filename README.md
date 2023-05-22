# common-model

Shared models and support systems for [Doctrine](https://www.doctrine-project.org/) ORM entities.

### Generating Entities

After installing Doctrine, update the package config at _config/packages/doctrine.yaml_.

```yaml
doctrine:
  ...
  orm:
    auto_generate_proxy_classes: true
    naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
    auto_mapping: true
    mappings:
      App:
        is_bundle: false
        type: yml
        dir: '%kernel.project_dir%/config/doctrine' # <---- this is where our generated entity config files will live.
        prefix: 'App\Entity'
        alias: App
...
```

Entities can be generated from an existing schema using the following command: `php bin/console doctrine:mapping:import 'App\Entity' yml --path=config/doctrine`.

You can limit which entities you generated by adding a `--filter=EntityClassName` to the above command.

### Abstractions

This library contains a handful of helpful abstractions for handling common entity use-cases like dated entities, status support, and handlers for persistence.

#### Traits

**Dated**: provides support for date_created and date_updated fields, and includes lifecycle methods for handling changes to these fields.

**Statused**: provides support for status fields, and includes lifecycle methods for handling changes to these fields.

**Respondent**: adds `toString()` method to entities, and provides a `getResponse()` method that can convert entities into a JSON.

**Persistent**: adds helper methods for single-entity persistence, and exposes lifecycle methods for entities.

### Metadata

Many entities in our database systems need to support arbitrary "metadata".  Users may have addresses or favourite colours, for example.

In order to support a wide array of custom attributes for each application that uses these packages, we've exposed a suite of helpful abstractions for supporting "meta" tables, following a common schema and design, and utilizing the [Entity-Attribute-Value](https://en.wikipedia.org/wiki/Entity%E2%80%93attribute%E2%80%93value_model) database model.

Using the above example of a "User" entity, matching a "user" database table, we would also create a "UserMeta"/"user_meta" table.

This entity would extend the _Entity\Abstraction\BaseMeta_ class, which will expose a common set of functionality shared between all "meta" tables.

Each "meta" table should have the following columns/properties:
* id (bigint) // identifier/primary key
* <FKEY_ID> (int) // foreign-key link to master table
* field (varchar) // period-delimited name
* value1 (varchar) // primary value
* value2 (varchar) // secondary value (optional)
* date_created (datetime)
* date_updated (datetime)

a full example of the above for a user_meta model would be:

| id | user_id | field           | value1 | value2 | date_created        | date_updated         |
|----|---------|-----------------|--------|--------|---------------------|----------------------|
| 1  | 1       | address.city    | Ottawa | NULL   | 2023-05-17 10:14:00 | 2023-05-22 10:14:00  |
| 2  | 2       | favourite_color | green  | NULL   | 2023-05-19 12:09:00 | 2023-05-22 10:14:00  |

### Repositories

Searching for database entries in Doctrine makes use of "repository" classes, which manage the lifecyle process for querying the database, as well as holding the results.

By default, Doctrine supports virtual classes for all entities that provide simple methods like `find()` and `findBy()`, which allow querying for rows through an ID or array of criteria, respectively.

If we want to add custom query methods, we must define and register our own repository classes.

Custom repositories should extend the _Repository\Abstraction\BaseRepository_ class, and implement the `getEntityClassName()` method, which must return the FQCN of the entity this repository manages:

```php
<?php

...

class UserRepository extends BaseRepository 
{
    public function getEntityClassName(): string
    {
        return User::class;
    }
    
    ...
}
```

After creating the repository class, it must be registered with Doctrine in the _config/doctrine/User.orm.yml_ file.
```yaml
App\Entity\User:
  type: entity
  repositoryClass: App\Repository\UserRepository # my custom repository class.
  table: user
...
```

From here, we can call any custom methods we add to our repository.  Additionally, any repository extending from our base will automatically support paging and ordering.

#### Searching

We can add custom search functionality to any entity through our repository classes by making use of the _Service\Abstraction\BaseSearchService_ class. 

Extending from this class will expose functionality to search for entities using an expression syntax that ties alias' to table columns as a key:value pair.

Once implemented, the search service can be injected into a custom repository using Symfony's Dependency Injection system, and then exposed through a custom `search()` method.

A full search service implementation could might look like this:
```php
<?php

...

class UserSearchService extends BaseSearchService 
{
    protected function getSearchExpression(): string
    {
        return '/[nds]:"[^"]*"|\S+/';
    }
    
    protected function getSearchInnerQuery(): string
    {
        return "
            SELECT
            ...
        ";
    }
    
    protected function evaluateCriteriaAsSql(array $criteria): string
    {
        $sql = '';
        
        foreach($criteria as $field => $values) {
            // field keys match the regular expression from getSearchExpression().
            switch($field) {
                case "n": 
                    $sql .= sprintf("AND (q.name = %s)", $values[0]);
                    
                    break;
                    
                case "d":
                    ...
                
                case "s":
                    ...
            }
        }
    }
}
```
