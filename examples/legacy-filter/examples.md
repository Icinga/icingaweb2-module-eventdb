Legacy Filter JSON examples
===========================

Here are some JSON examples, each line is a single filter used in `edb_filter` custom variable.

    { host: 'otherhostname' }
    { host: 'specialhostname', priorityExclusion: [] }
    { "host": ".*", "programInclusion": ["cloud-monitoring"] }
    { programInclusion: ['test-program'] }
