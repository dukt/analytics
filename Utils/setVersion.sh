#!/bin/bash

cat > Source/videos/Info.php << EOF
<?php

namespace Craft;

define('ANALYTICS_VERSION', '$PLUGIN_VERSION.$BUILD_NUMBER');

EOF
