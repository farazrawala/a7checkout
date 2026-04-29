<?php
// Test .htaccess syntax and Apache modules
// Delete this file after testing for security reasons

echo "<h2>Apache .htaccess Diagnostic</h2>";
echo "<hr>";

// Check Apache version
if (function_exists('apache_get_version')) {
    echo "<p><strong>Apache Version:</strong> " . apache_get_version() . "</p>";
} else {
    echo "<p><strong>Apache Version:</strong> Unable to detect (function not available)</p>";
}

// Check loaded modules
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<h3>Loaded Apache Modules:</h3>";
    echo "<ul>";
    
    $required_modules = ['mod_auth_basic', 'mod_authn_file', 'mod_authz_core', 'mod_authz_user'];
    foreach ($required_modules as $module) {
        $loaded = in_array($module, $modules);
        if ($loaded) {
            echo "<li style='color:green;'>✓ $module - LOADED</li>";
        } else {
            echo "<li style='color:red;'>✗ $module - NOT LOADED (This could cause 500 error!)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p><strong>Note:</strong> Cannot check Apache modules (function not available)</p>";
    echo "<p>Required modules for Basic Auth:</p>";
    echo "<ul>";
    echo "<li>mod_auth_basic</li>";
    echo "<li>mod_authn_file</li>";
    echo "<li>mod_authz_core</li>";
    echo "<li>mod_authz_user</li>";
    echo "</ul>";
}

// Check .htpasswd file
$htpasswd_path = '/home/prodigywebz/basicauth.prodigywebz.com/.htpasswd';
echo "<hr>";
echo "<h3>.htpasswd File Status:</h3>";
if (file_exists($htpasswd_path) && is_readable($htpasswd_path)) {
    echo "<p style='color:green;'>✓ File exists and is readable</p>";
} else {
    echo "<p style='color:red;'>✗ File missing or not readable</p>";
}

// Check if .htaccess exists
echo "<hr>";
echo "<h3>.htaccess File Status:</h3>";
$htaccess_path = $_SERVER['DOCUMENT_ROOT'] . '/.htaccess';
if (file_exists($htaccess_path)) {
    echo "<p style='color:green;'>✓ .htaccess file exists</p>";
    echo "<p><strong>Location:</strong> " . $htaccess_path . "</p>";
    
    // Show first few lines (be careful not to expose sensitive info)
    $content = file_get_contents($htaccess_path);
    $lines = explode("\n", $content);
    echo "<p><strong>First 10 lines of .htaccess:</strong></p>";
    echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ddd;'>";
    echo htmlspecialchars(implode("\n", array_slice($lines, 0, 10)));
    echo "</pre>";
} else {
    echo "<p style='color:orange;'>⚠ .htaccess file not found at: " . $htaccess_path . "</p>";
}

echo "<hr>";
echo "<h3>Recommendations:</h3>";
echo "<ul>";
echo "<li>Check Apache error log in cPanel: <strong>Metrics → Errors</strong></li>";
echo "<li>Look for errors mentioning 'AuthUserFile' or 'htpasswd'</li>";
echo "<li>If modules are missing, contact your hosting provider</li>";
echo "<li>Try the alternative .htaccess syntax provided</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>IMPORTANT:</strong> Delete this file after testing for security!</p>";
?>

