Security
============================

The EventDB Module provides permissions and restrictions as described below.

## Permissions

| Name             | Description |
| ---------------- | ----------- |
| eventdb/events   | Allow to view events |
| eventdb/comments | Allow to view comments |
| eventdb/interact | Allow to acknowledge and comment events |

## Restrictions

| Name                    | Description |
| ----------------------- | ----------- |
| eventdb/events/filter   | Restrict views to the events that match the filter |
| eventdb/comments/filter | Restrict views to the comments that match the filter |

## Examples

| eventdb/events/filter      | Description |
| -------------------------- | ----------- |
| type!=syslog               | Hide the Syslog events from a role |
| type=syslog&program=icinga | Show only Icinga-related Syslog events to a role |
