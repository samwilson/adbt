A Database Thing
================

This is a work in progress.  Please ignore it for now.

Installation
------------

1. Copy all files to a web-accessible location
2. Copy `config.dist.php` to `config.php` and edit at least the database name
   variable `$database_config['database']`
3. Browse to the above location

With no other configuration, you will be able to log in as any valid database
user and do all that that user is permitted to do.

Extending and Customising
-------------------------

ADBT can be extended with 'modules', and those modules extended in the same way.

1. Create a separate directory outside of the ADBT installation

2. In `index.php` in this directory, set the include_path to include the path of the new directory and the path of ADBT:

    set_include_path('.'.PATH_SEPARATOR.__DIR__.PATH_SEPARATOR.get_include_path());

   (Notice that the current directory is still first in the include path; this matters.)

3. Include the `index.php` that's in the ADBT directory:

    require_once '../adbt/index.php';

4. Create, configure, and run the app:

    $app = new ADBT_App();
    $app->setModules(array('CustomModule', 'ADBT')); # Where 'CustomModule' is your module's base name.
    $app->run();

Authentication and Authorisation
--------------------------------

The core system doesn't do any authorisation. This should be handled by custom systems.

Choose between DB, LDAP, or Local authentication:
* If no DB username is provided, authenticate as a DB user;
* If there is a DB username in Config, but no LDAP hostname, use Local (i.e. a 'users' table with username and password columns);
* Lastly, if there's a DB username and a LDAP hostname, use LDAP.

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
