extjs-bundle
============

Use ExtJs with Symfony 2

![Build Status](https://travis-ci.org/jamesmoey/extjs-bundle.png?branch=2.3)

Packagist: https://packagist.org/packages/tpg/extjs-bundle

The aim of this bundle is to ease the intergration between Symfony 2 and ExtJS client side framework. It support
 - Dynamic runtime generation of Ext.data.Model based on entities/models implement on the server side.
 - Auto integrate of Ext Remoting integration with Symfony 2 Controller.
 - Code generation of Rest Controller per entities.

Requirement
-----------
Mandatory
 - Symfony 2.3.*
 - Serializer library from JMS 0.12.*
 - Doctrine ORM
 - Generator from Sensio 2.3.*

Optional
 - Rest Controller code generator need FOSRestBundle 0.12.*

Installation
------------
**Using composer**
``` bash
$ composer require tpg/extjs-bundle
```

**Enabling bundle**
``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new \Tpg\ExtjsBundle\TpgExtjsBundle(),
    );
}
```

**Add routing rules**
``` yml
# app/config/routing.yml
tpg_extjs:
  resource: "@TpgExtjsBundle/Resources/config/routing.yml"
  prefix:   /extjs
```

All documentation below, we assume all ExtJs controller path is prefix with /extjs.

Configuration
-------------
``` yaml
tpg_extjs:
    entities:
        - @AcmeDemoBundle/Entity/
        - @AcmeDemoBundle/Model/Auto
    remoting:
        bundles:
            - AcmeDemoBundle
```

Testing Run
-----------
Unit Test are written with PHPUnit and Jasmine JS. How to run unit test is in .travis.yml file.

Model/Entities Code Generation
------------------------------
You may need to configure the additional routing rule for this feature to work. To generate Ext.data.Model code, you just
need to include script tag pointing to generateModel.js

To generate all entities configured in the configuration,
``` html
<script type="text/javascript" src="/extjs/generateModel.js"></script>
```

To generate some specific entities,
``` html
<script type="text/javascript" src="/extjs/generateModel.js?model[]=Acme.DemoBundle.Entity.Person&model[]=Test.TestBundle.Model.Book"></script>
```
Acme.DemoBundle.Entity.Person and Test.TestBundle.Model.Book is the full namespace of the model, just replace slash (\)
with dot (.).

The entity class must annotate with Tpg\ExtjsBundle\Annotation\Model, please check out
Tpg/ExtjsBundle/Tests/Fixtures/Test/TestBundle/Model/*.php for example usage.

There is a Twig extension (extjs_model) to make it easy to include/load model onto/from the current page.

To generate and inject the ExtJS code onto the current page,
``` twig
{{ extjs_model(true, 'Acme.DemoBundle.Entity.Person', 'Test.TestBundle.Model.Book') }}
```

To reference and load ExtJS code through script tag,
``` twig
{{ extjs_model(false, 'Acme.DemoBundle.Entity.Person', 'Test.TestBundle.Model.Book') }}
```

Remoting integration
--------------------
You will need to configure remoting parameter to get ExtJs Remoting working with Controller.

To generate the glue for the remoting intergation on the page, just include
``` html
<script type="text/javascript" src="/extjs/remoteapi.js"></script>
```

To enable a controller's action remotable, you need to annotate the function with Tpg\ExtjsBundle\Annotation\Direct.

Code generation of Rest Controller
----------------------------------
The rest controller code generation is an extension of Sensio's controller generator. The generated controller will extend
FOS\RestBundle\Controller\FOSRestController class.

To generate a rest controller (PeopleController) for entity Acme.DemoBundle.Entity.Person,
``` bash
php app/console generate:rest:controller --controller AcmeDemoBundle:People --entity AcmeDemoBundle:Person
```

Only controller and entity option is require, all the rest of the option can be left as default. The generator will
create/update 2 files,
  - Acme\DemoBundle\Controller\PeopleControler will be generated.
  - Acme\DemoBundle\Resources\config\routing.rest.yml will be updated or created

To include this generated rest controller into the routing table, just include
``` yml
# app/config/routing.yml
acmedemo_api_rest:
  resource: "@AcemeDemoBundle/Resources/config/routing.rest.yml"
  prefix: /api
  type: rest
```