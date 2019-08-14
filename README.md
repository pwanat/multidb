CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration


INTRODUCTION
------------

This is Multidb Module made for the purpose of recruitment process.
Configure northwind database under:



REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

Install the Multidb module as you would normally install a contributed Drupal
module. Visit https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
--------------

    1. Navigate to Administration > Extend and enable the Multidb module.
    2. Navigate to Configuration > Multidb Configuration
        -  configure northwind database connection
    3. Navigate to Configuration > Multidb Configuration > API Access
        -  give users access to API orders pages
    7. Navigate to People > Permissions
        -  give 'Access HTML Orders' permission to selected roles


USAGE
--------------

View orders in HTML: orders/{order-nr}
View orders via API: api/orders/{order-nr}
