About
=====

Web Service Data is a collection of modules allowing you to interact with web services using entities and fields in Drupal.

Modules/Sub-Modules
==========

Web Service Data (wsdata)
-------------------------

- Defines abstract classes for Web Service Connectors (WsConnector) and Web Service Data Processors (WsData).

Web Service Configuration (wsconfig)
------------------------------------

- Defines an entity type called "wsconfig_type" which stores information about how the URL to the root of a web service and which connector type to use (ex: RESTClient for REST based services).

- Defines an entity called "wsconfig" which stores information about how to interact with a given service (ex: /service/user) and each of the CRUD operations.

Web Service Fields Storage (wsfields_storage)
---------------------------------------------

- Defines a field storage controller which uses the CRUD operations defined in a wsconfig to interact with remote data using Drupal Fields.

Web Service Fields (wsfields)
-----------------------------

- Contains implementations of the core fields to parse data coming from an implementation of "WsData" into the proper format expected by each field type.

Web Service Entities (wsentities)
---------------------------------

**Not Yet Implemented**

Web Service Services (ws_services)
----------------------------------

- Sample implementation on connecting to web services of another Drupal site which is sharing data using the Services module.

- Includes a test suite for the wsdata module as a whole

Web Service Fields - Address Field (ws_addressfield)
----------------------------------------------------

todo

Web Service Fields - Date Field (ws_datefield)
----------------------------------------------

todo

Web Service Fields - Entity Reference Field (ws_entityreferencefield)
---------------------------------------------------------------------

todo

Installation
============

todo

Usage
=====

todo

Example Implementations
=======================

See 'ws_services' module for an example implementation.

Connectors
----------

You can use the [RESTClient](https://github.com/coldfrontlabs/restclient) module connector as a sample to build your connectors with. *This is not the same as the [REST Client](http://drupal.org/project/restclient) module found on Drupal.org*

Processors
----------

TODO

History
=======

Below is an explanation of how and why wsdata came into existence and the types of solutions it can provide to developers working with Drupal and web services.

The Problem
-----------

Drupal gives developers and site builders a lot of power in its Entity and Field APIs. However, the default implementations assume that all your data exists within Drupal's tables; that Drupal is the primary data source.

When dealing with enterprises with a historical data set, it isn't always feasible or practical to migrate and/or copy this data into Drupal.

In these cases you have two options; build "dumb views" on the data. To display it to the user or expose it to parts of Drupal you write custom code to load the information from the service into custom tables or as temporarily cached data. But you lose all of the power and flexibility of having the data exist as entities/fields.

In the second case, you build a custom bit of code to import the data as entities (ex: nodes, comments, users) which gives you the use of the Entity and Field APIs but you're now disconnected from the data source. You need to maintain a data sync or run batch updates on data in Drupal and/or in the original data set. This also brings up the issue of "big data" in Drupal. The default table structure in Drupal normalizes the data. Which means running updates may be very expensive.

Not to mention, in the second case, most of this code is single purpose and not necessarily reusable.

Goal
----

Based on the problems above with the two current implementations we set out to make a solution that both allows the use of the Entity and Field APIs in Drupal and avoided the issue of syncing data and performing batch updates.

We also wanted to ensure that the solution would be compatible with existing contrib modules in Drupal. Meaning you could treat data from web services as you would data from Drupal's own tables.

Lastly, we wanted to ensure that the solution was reusable. Meaning it could be repurposed to work with nearly any web service with nearly any kind of data being returned.


Initial Concept Implementation
------------------------------

We came up with three components to generically describe each portion required for interacting with web service data.

- Connector: A tool or library which handled the request medium (ex: HTTP)
- Processor: An object which would convert the data from the web service into a standard format which could be interpreted by Drupal
- Configuration Type: An entity which told Drupal which connector to use with which web service, which processor to use to parse the data and what data we wanted to extract from that service




Issues with the Concept
-----------------------

Second Attempt
--------------
@TODO

How it works
------------

Future
======
