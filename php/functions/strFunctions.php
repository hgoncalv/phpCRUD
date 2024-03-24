<?php

function apply_sanitization($data)
{
    // Allow null values without further processing
    if ($data === null) {
        return null;
    }
    $data = strip_tags($data);
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    $data = trim($data);
    return $data;
}

function hg_sanitize($input_data)
{
    if (!$input_data) {
        return;
    }

    foreach ($input_data as $key => $value) {
        if (is_string($value)) {
            $input_data[$key] = apply_sanitization($value);
        } elseif (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                if (is_string($subValue)) {
                    $value[$subKey] = apply_sanitization($subValue);
                }
            }
            $input_data[$key] = $value;
        }
    }
    return $input_data;
}
