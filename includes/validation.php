<?php
/**
 * Validation Functions
 */

/**
 * Validate required field
 */
function validateRequired($value, $fieldName = 'Field') {
    if (empty(trim($value))) {
        return "$fieldName is required";
    }
    return true;
}

/**
 * Validate email
 */
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return true;
}

/**
 * Validate number
 */
function validateNumber($value) {
    if (!is_numeric($value)) {
        return "Must be a valid number";
    }
    return true;
}

/**
 * Validate min length
 */
function validateMinLength($value, $min) {
    if (strlen($value) < $min) {
        return "Must be at least $min characters";
    }
    return true;
}

/**
 * Validate max length
 */
function validateMaxLength($value, $max) {
    if (strlen($value) > $max) {
        return "Must not exceed $max characters";
    }
    return true;
}

/**
 * Validate min number
 */
function validateMinNumber($value, $min) {
    if ($value < $min) {
        return "Must be at least $min";
    }
    return true;
}

/**
 * Validate max number
 */
function validateMaxNumber($value, $max) {
    if ($value > $max) {
        return "Must not exceed $max";
    }
    return true;
}

/**
 * Validate field based on rules
 */
function validateField($value, $field) {
    $errors = [];
    
    // Check required
    if ($field['required'] && empty($value)) {
        $errors[] = $field['label'] . ' is required';
        return $errors; // Return early if required field is empty
    }
    
    // Skip other validations if value is empty and not required
    if (empty($value) && !$field['required']) {
        return $errors;
    }
    
    // Validate email type
    if ($field['field_type'] === 'email') {
        $result = validateEmail($value);
        if ($result !== true) {
            $errors[] = $field['label'] . ': ' . $result;
        }
    }
    
    // Validate number type
    if ($field['field_type'] === 'number') {
        $result = validateNumber($value);
        if ($result !== true) {
            $errors[] = $field['label'] . ': ' . $result;
        }
    }
    
    // Validate custom rules
    if (!empty($field['validation_rules'])) {
        $rules = json_decode($field['validation_rules'], true);
        
        if (isset($rules['min_length'])) {
            $result = validateMinLength($value, $rules['min_length']);
            if ($result !== true) {
                $errors[] = $field['label'] . ': ' . $result;
            }
        }
        
        if (isset($rules['max_length'])) {
            $result = validateMaxLength($value, $rules['max_length']);
            if ($result !== true) {
                $errors[] = $field['label'] . ': ' . $result;
            }
        }
        
        if (isset($rules['min_number']) && $field['field_type'] === 'number') {
            $result = validateMinNumber($value, $rules['min_number']);
            if ($result !== true) {
                $errors[] = $field['label'] . ': ' . $result;
            }
        }
        
        if (isset($rules['max_number']) && $field['field_type'] === 'number') {
            $result = validateMaxNumber($value, $rules['max_number']);
            if ($result !== true) {
                $errors[] = $field['label'] . ': ' . $result;
            }
        }
    }
    
    return $errors;
}

/**
 * Validate form submission
 */
function validateFormSubmission($fields, $postData, $files) {
    $errors = [];
    
    foreach ($fields as $field) {
        $fieldName = $field['field_name'];
        $value = '';
        
        // Handle different field types
        if ($field['field_type'] === 'file') {
            if (isset($files[$fieldName]) && $files[$fieldName]['error'] !== UPLOAD_ERR_NO_FILE) {
                // File validation will be done during upload
                continue;
            } elseif ($field['required']) {
                $errors[] = $field['label'] . ' is required';
            }
            continue;
        } elseif ($field['field_type'] === 'checkbox') {
            $value = isset($postData[$fieldName]) ? $postData[$fieldName] : [];
            if ($field['required'] && empty($value)) {
                $errors[] = $field['label'] . ' is required';
            }
            continue;
        } else {
            $value = isset($postData[$fieldName]) ? trim($postData[$fieldName]) : '';
        }
        
        $fieldErrors = validateField($value, $field);
        $errors = array_merge($errors, $fieldErrors);
    }
    
    return $errors;
}
