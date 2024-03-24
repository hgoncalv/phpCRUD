<?php
// Function to parse the .env file
function parseEnv($filePath)
{
    $envVariables = [];

    // Check if the file exists and is readable
    if (file_exists($filePath) && is_readable($filePath)) {
        // Read each line of the file
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignore lines starting with '#' (comments) or lines without '=' delimiter
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            // Split each line by '=' delimiter
            list($name, $value) = explode('=', $line, 2);

            // Remove leading/trailing whitespace and quotes from value
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            // Store the variable in the array
            $envVariables[$name] = $value;
        }
    }

    return $envVariables;
}
