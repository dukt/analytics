#!/bin/bash

cd ./Source/analytics/

# Create Info.php with plugin version constant

cat > Info.php << EOF
<?php
namespace Craft;

define('ANALYTICS_VERSION', '${PLUGIN_VERSION}');

EOF