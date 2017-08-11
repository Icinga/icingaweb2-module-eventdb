Custom Variables
================

The monitoring integration of this module can use custom variables from the
Icinga context to control display and filtering of the integration.

Also see [Configuration](02-Configuration.md) on how to configure the features.

Custom variables control:

* If the EventDB integration and actions are shown for a host or service
* How the linked results should be filtered

## Examples

For Icinga 2:

```icinga2
object Host "test" {
  import "generic-host"
  
  address = "127.0.0.1"
  
  vars.edb = "1"
  vars.edb_filter = "priority!=7&priority!=5&priority!=6&ack=0"
  // ...
}
```

For Icinga 1.x:

```nagios
define host {
  use         generic-host
  host_name   test
  
  address 127.0.0.1
  
  _edb        1
  _edb_filter priority!=7&priority!=5&priority!=6&ack=0
}
```

Some other filter examples:




## Legacy filters

The module also supports legacy JSON filters from the icinga-web 1.x EventDB module.

Please see the `examples` directory of this module for some supported filters.
