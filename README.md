# EventDB Module for Icinga Web 2

## About

With the EventDB Module you can browse, comment and acknowledge events collected
by [EventDB](https://git.netways.org/eventdb/eventdb) easily in
[Icinga Web 2](https://www.icinga.org/products/icinga-web-2/).

## Requirements

* Icinga Web 2
* A database with events collected by EventDB

## Configuration

To let the module know where the events are stored you have to create an SQL
database resource with a database with events collected by EventDB.
Once you have installed and enabled the module, go to the EventDB Module's
configuration and select the database resource as backend.

## Monitoring Integration

The EventDB module integrates into Icinga Web 2's monitoring module by default,
offering action links in host and service detail views.

### Default actions

By default, every host and services shows an action link to the event list, 
filtered by host name.

### Custom Variable

You can configure a custom variable that enables the integration selectively.

* `_edb` or `vars.edb` will enable the actions only on objects that have the custom var
* `_edb_filter` or `vars.edb_filter` allows you to pre-filter the linked events

The name of the customvar (`edb`) needs to be configured in the config area of the module.

### Filters (TODO)

### Always show actions

There are options to always show actions on host or service, even if the custom variable
is not set.

## Security

The EventDB Module provides permissions and restrictions as described below.

### Permissions

| Name             | Description |
| ---------------- | ----------- |
| eventdb/events   | Allow to view events |
| eventdb/comments | Allow to view comments |
| eventdb/interact | Allow to acknowledge and comment events |

### Restrictions

| Name                    | Description |
| ----------------------- | ----------- |
| eventdb/events/filter   | Restrict views to the events that match the filter |
| eventdb/comments/filter | Restrict views to the comments that match the filter |

### Examples

| eventdb/events/filter      | Description |
| -------------------------- | ----------- |
| type!=syslog               | Hide the Syslog events from a role |
| type=syslog&program=icinga | Show only Icinga-related Syslog events to a role |
