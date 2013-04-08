A Database Thing
================

This is a work in progress.  Please ignore it for now.

Installation
------------

Extending and Customising
-------------------------

ADBT can be extended with 'modules', and those modules extended in the same way.

# Create a separate directory outside of the ADBT installation
# In `index.php` in this directory, set the include_path to include the path of the new directory and the path of ADBT:
  set_include_path('.'.PATH_SEPARATOR.dirname(__FILE__).PATH_SEPARATOR.get_include_path());
  (Notice that the current directory is still first in the include path; this matters.)
# Include index.php from the ADBT directory

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
