# Custom Age Gate Integration Guide

This guide explains how to create custom age gate pages using the enhanced Craft Age Gate plugin with all available data and helper functions.

## Template Override System

The plugin supports template overrides, allowing you to create custom age gate templates while maintaining the plugin's functionality as a fallback.

### Creating Custom Templates

To create a custom age gate template:

1. **Create the template directory**: `templates/age-gate/`
2. **Create the template file**: `templates/age-gate/index.twig`
3. **Use the example template**: Copy `example-custom-template.twig` as a starting point

The plugin will automatically use your custom template if it exists, otherwise it falls back to the plugin's default template.

### Template Override Detection

You can check if a custom template exists using Twig:

```twig
{% if craft.ageGate.hasCustomTemplate() %}
    {# Custom template is being used #}
    <p>Using custom template: {{ craft.ageGate.getTemplatePath() }}</p>
{% else %}
    {# Plugin template is being used #}
    <p>Using plugin template: {{ craft.ageGate.getTemplatePath() }}</p>
{% endif %}
```

## Available Data for Custom Age Gate Pages

### Twig Variables

When creating custom age gate pages, you have access to the following Twig variables:

#### Basic Data Access
```twig
{# Get all age gate data in one array #}
{% set ageGateData = craft.ageGate.getAgeGateData() %}

{# Individual data access #}
{% set settings = craft.ageGate.getSettings() %}
{% set originalUrl = craft.ageGate.getOriginalUrl() %}
{% set minAge = craft.ageGate.getMinAge() %}
{% set cookieName = craft.ageGate.getCookieName() %}
{% set declineUrl = craft.ageGate.getDeclineUrl() %}
{% set isVerified = craft.ageGate.isAgeVerified() %}
```

#### Formatted Content
```twig
{# Get formatted content with placeholders replaced #}
{{ craft.ageGate.getFormattedTitle() }}
{{ craft.ageGate.getFormattedContent()|raw }}
{{ craft.ageGate.getFormattedAgreeButton() }}
{{ craft.ageGate.getFormattedDeclineButton() }}
```

#### Policy Links
```twig
{# Get policy page URLs #}
{% set privacyUrl = craft.ageGate.getPrivacyPolicyUrl() %}
{% set cookieUrl = craft.ageGate.getCookiePolicyUrl() %}
```

### JavaScript Variables

The plugin automatically registers several JavaScript variables:

```javascript
// Basic settings (legacy)
window.agegatesettings

// Enhanced data object
window.ageGateData

// Original URL before redirect
window.originalSrcUrl

// Helper functions availability flag
window.ageGateHelpersAvailable
```

### JavaScript Helper Functions

The plugin provides a comprehensive set of helper functions:

```javascript
// Age calculation and verification
window.AgeGateHelpers.calculateAge(birthdate)
window.AgeGateHelpers.verifyAge(birthdate)

// Cookie management
window.AgeGateHelpers.setCookie(name, value, days)
window.AgeGateHelpers.getCookie(name)
window.AgeGateHelpers.isAgeVerified()

// Navigation
window.AgeGateHelpers.redirectToOriginal(fallback)
window.AgeGateHelpers.redirectToDecline()
window.AgeGateHelpers.verifyAndRedirect(fallback)

// Text formatting
window.AgeGateHelpers.formatText(text)

// Form initialization
window.AgeGateHelpers.initAgeGateForm(formSelector, dateInputSelector, submitButtonSelector)
```

## Example Implementations

### Basic Custom Age Gate Page

```twig
{% set ageGateData = craft.ageGate.getAgeGateData() %}
{% set originalUrl = craft.ageGate.getOriginalUrl() %}

<!DOCTYPE html>
<html>
<head>
    <title>{{ craft.ageGate.getFormattedTitle() }}</title>
</head>
<body>
    <h1>{{ craft.ageGate.getFormattedTitle() }}</h1>
    <p>{{ craft.ageGate.getFormattedContent()|raw }}</p>
    
    <form id="age-gate-form">
        <input type="date" id="birthdate" required>
        <button type="submit">{{ craft.ageGate.getFormattedAgreeButton() }}</button>
        <a href="{{ craft.ageGate.getDeclineUrl() }}">{{ craft.ageGate.getFormattedDeclineButton() }}</a>
    </form>

    <script>
        document.getElementById('age-gate-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const birthdate = document.getElementById('birthdate').value;
            if (window.AgeGateHelpers.verifyAge(birthdate)) {
                window.AgeGateHelpers.verifyAndRedirect();
            } else {
                window.AgeGateHelpers.redirectToDecline();
            }
        });
    </script>
</body>
</html>
```

### Advanced Custom Age Gate with All Features

```twig
{% set ageGateData = craft.ageGate.getAgeGateData() %}
{% set originalUrl = craft.ageGate.getOriginalUrl() %}

<!DOCTYPE html>
<html lang="{{ craft.app.language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ craft.ageGate.getFormattedTitle() }}</title>
    <style>
        /* Your custom styles */
        .age-gate-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="age-gate-container">
        <h1>{{ craft.ageGate.getFormattedTitle() }}</h1>
        <div class="content">
            {{ craft.ageGate.getFormattedContent()|raw }}
        </div>
        
        <form id="age-gate-form">
            <div class="form-group">
                <label for="birthdate">Enter your date of birth:</label>
                <input type="date" id="birthdate" name="birthdate" required max="{{ "now"|date("Y-m-d") }}">
                <div class="error-message" id="error-message" style="display: none;">
                    You must be at least {{ craft.ageGate.getMinAge() }} years old to enter this site.
                </div>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    {{ craft.ageGate.getFormattedAgreeButton() }}
                </button>
                <a href="{{ craft.ageGate.getDeclineUrl() }}" class="btn btn-secondary">
                    {{ craft.ageGate.getFormattedDeclineButton() }}
                </a>
            </div>
        </form>
        
        {% if craft.ageGate.getPrivacyPolicyUrl() or craft.ageGate.getCookiePolicyUrl() %}
            <div class="links">
                {% if craft.ageGate.getPrivacyPolicyUrl() %}
                    <a href="{{ craft.ageGate.getPrivacyPolicyUrl() }}">Privacy Policy</a>
                {% endif %}
                {% if craft.ageGate.getPrivacyPolicyUrl() and craft.ageGate.getCookiePolicyUrl() %}
                    <span> • </span>
                {% endif %}
                {% if craft.ageGate.getCookiePolicyUrl() %}
                    <a href="{{ craft.ageGate.getCookiePolicyUrl() }}">Cookie Policy</a>
                {% endif %}
            </div>
        {% endif %}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if already verified
            if (window.AgeGateHelpers && window.AgeGateHelpers.isAgeVerified()) {
                window.AgeGateHelpers.redirectToOriginal();
                return;
            }
            
            // Initialize form
            const form = document.getElementById('age-gate-form');
            const birthdateInput = document.getElementById('birthdate');
            const errorMessage = document.getElementById('error-message');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const birthdate = birthdateInput.value;
                if (!birthdate) {
                    showError('Please enter your date of birth.');
                    return;
                }
                
                if (window.AgeGateHelpers && window.AgeGateHelpers.verifyAge(birthdate)) {
                    window.AgeGateHelpers.verifyAndRedirect();
                } else {
                    showError();
                }
            });
            
            // Hide error when user starts typing
            birthdateInput.addEventListener('input', function() {
                errorMessage.style.display = 'none';
            });
            
            function showError(customMessage) {
                errorMessage.style.display = 'block';
                if (customMessage) {
                    errorMessage.textContent = customMessage;
                }
                birthdateInput.focus();
            }
        });
    </script>
</body>
</html>
```

## Using the Enhanced Redirect Template

The plugin now includes an enhanced redirect template that provides a complete age gate implementation out of the box. To use it:

1. Set your age gate display type to "redirect" in the plugin settings
2. The plugin will automatically render the enhanced template with all functionality
3. The template includes automatic age verification, cookie management, and redirect handling

## Available Data Structure

The `ageGateData` array contains:

```php
[
    'settings' => SettingsRecord,           // All plugin settings
    'originalUrl' => string|null,           // Original URL before redirect
    'isVerified' => bool,                  // Whether age is already verified
    'privacyPolicyUrl' => string|null,     // Privacy policy page URL
    'cookiePolicyUrl' => string|null,      // Cookie policy page URL
    'minAge' => int,                       // Minimum age required
    'cookieName' => string,                // Cookie name for verification
    'declineUrl' => string,                // URL to redirect to if declined
    'title' => string,                     // Age gate title
    'content' => string,                   // Age gate content
    'agreeButton' => string,               // Agree button text
    'declineButton' => string,             // Decline button text
    'displayType' => string,               // Display type (modal/redirect)
    'isEnabled' => bool,                   // Whether age gate is enabled
    'formattedTitle' => string,            // Title with placeholders replaced
    'formattedContent' => string,          // Content with placeholders replaced
    'formattedAgreeButton' => string,      // Agree button with placeholders replaced
    'formattedDeclineButton' => string,    // Decline button with placeholders replaced
]
```

## Template Override Best Practices

1. **Always include the plugin's JavaScript** for helper functions
2. **Use the same data variables** as the plugin template for consistency
3. **Test both custom and plugin templates** to ensure functionality
4. **Keep the same form structure** for compatibility with helper functions
5. **Include error handling** for template rendering issues
6. **Use the example template** as a starting point

## Best Practices

1. **Always check if age is already verified** before showing the age gate
2. **Use the helper functions** for consistent behavior across different implementations
3. **Handle errors gracefully** with user-friendly messages
4. **Include policy links** when available for legal compliance
5. **Test with different age scenarios** to ensure proper validation
6. **Use the formatted content methods** to ensure placeholders are properly replaced
7. **Create custom templates** in the `templates/age-gate/` directory for overrides

## Troubleshooting

### Common Issues

1. **Helper functions not available**: Ensure the plugin's JavaScript is loaded
2. **Original URL not found**: Check that the session is properly maintained
3. **Cookie not being set**: Verify cookie name and domain settings
4. **Age verification not working**: Check that the birthdate format is correct
5. **Custom template not loading**: Verify the template path is `templates/age-gate/index.twig`
6. **Template errors**: Check the Craft logs for template rendering errors
7. **Fallback not working**: Ensure the plugin template exists and is accessible

### Debug Information

You can access debug information through the browser console:

```javascript
console.log('Age Gate Data:', window.ageGateData);
console.log('Original URL:', window.originalSrcUrl);
console.log('Helper Functions Available:', window.ageGateHelpersAvailable);
```

This comprehensive integration guide provides everything needed to create custom age gate pages with full functionality and data access.
