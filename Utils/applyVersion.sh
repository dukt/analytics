#!/bin/bash

for VERSION in "$@"

do

# Create Info.php with plugin version constant

cat > Source/analytics/Info.php << EOF
<?php

namespace Craft;

define('ANALYTICS_VERSION', '$VERSION');

EOF

done
