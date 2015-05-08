#!/bin/bash


for BUILD_NUMBER in "$@"

do
cat > Source/analytics/Info.php << EOF
<?php

namespace Craft;

define('ANALYTICS_VERSION', '3.0.$BUILD_NUMBER');

EOF

done
