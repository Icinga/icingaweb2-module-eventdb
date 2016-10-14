# EventDB Module for Icinga Web 2

## About

With the EventDB Module you can browse, comment and acknowledge events collected
by [EventDB](https://www.netways.org/projects/eventdb) easily in
[Icinga Web 2](https://www.icinga.org/products/icinga-web-2/).

## Requirements

* Icinga Web 2
* A database with events collected by EventDB

## Configuration

To let the module know where the events are stored you have to create an SQL
database resource with a database with events collected by EventDB.
Once you have installed and enabled the module, go to the EventDB Module's
configuration and select the database resource as backend.

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
