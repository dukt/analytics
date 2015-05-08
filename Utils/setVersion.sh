#!/bin/bash


for PLUGIN_VERSION in "$@"

do
cat > Source/analytics/Info.php << EOF
<?php

namespace Craft;

define('ANALYTICS_VERSION', '$PLUGIN_VERSION');

EOF

done
