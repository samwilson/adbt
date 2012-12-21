A Database Thing
================

This is a work in progress.  Please ignore it for now.

Default Schema
--------------

    CREATE TABLE IF NOT EXISTS `permissions` (
        `id`            int(5)        NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `table_name`    varchar(65)   NOT NULL DEFAULT '*' COMMENT 'A single table name, or an asterisk to denote all tables.',
        `column_name`   varchar(1000) NOT NULL DEFAULT '*' COMMENT 'A comma-delimited list of table columns, or an asterisk to denote all columns.',
        `where_clause`  varchar(200)  NULL DEFAULT NULL COMMENT 'The SQL WHERE clause to use to determine row-level access.',
        `action`        ENUM('*','read','edit','create','delete','import','export') NOT NULL DEFAULT '*' COMMENT 'The permission that is being assigned (the asterisk denotes all).',
        `group`         varchar(65)   NOT NULL DEFAULT '*' COMMENT 'A single user-group name, or asterisk to denote ALL groups.'
    ) COMMENT 'User permissions on databases, tables, and/or rows.';

    CREATE TABLE IF NOT EXISTS `actions` (
        id int(2) NOT NULL PRIMARY KEY AUTO_INCREMENT,
        name varchar(30) NOT NULL
    ) COMMENT 'What actions are controlled by the permissions table.';

    ALTER TABLE `permissions` ADD FOREIGN KEY (`action`) REFERENCES `actions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

    INSERT INTO `actions` (`name`) VALUES
      ('Read'),
      ('Update'),
      ('Create'),
      ('Delete'),
      ('Import'),
      ('Export');

Simplified BSD License
----------------------

Copyright &copy; 2012, Sam Wilson.  All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
  list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this
  list of conditions and the following disclaimer in the documentation and/or
  other materials provided with the distribution.

This software is provided by the copyright holders and contributors "as is" and
any express or implied warranties, including, but not limited to, the implied
warranties of merchantability and fitness for a particular purpose are
disclaimed. In no event shall the copyright holder or contributors be liable for
any direct, indirect, incidental, special, exemplary, or consequential damages
(including, but not limited to, procurement of substitute goods or services;
loss of use, data, or profits; or business interruption) however caused and on
any theory of liability, whether in contract, strict liability, or tort
(including negligence or otherwise) arising in any way out of the use of this
software, even if advised of the possibility of such damage.
