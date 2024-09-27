function validateForm(event) {
    // Get the values of the input fields
    const resourceName = document.getElementById("installation_name").value;
    const awsAccessKey = document.getElementById("aws_access_key").value;
    const awsSecretKey = document.getElementById("aws_secret_key").value;
    const awsAccountId = document.getElementById("aws_account_id").value;

    // Regular expression to check for spaces
    const spaceRegex = /\s/;

    // Check for spaces in each field
    if (spaceRegex.test(resourceName)) {
        alert("RESOURCE NAME should not contain spaces.");
        event.preventDefault(); // Prevent form submission
        return false;
    }
    if (spaceRegex.test(awsAccessKey)) {
        alert("AWS ACCESS KEY should not contain spaces.");
        event.preventDefault(); // Prevent form submission
        return false;
    }
    if (spaceRegex.test(awsSecretKey)) {
        alert("AWS SECRET KEY should not contain spaces.");
        event.preventDefault(); // Prevent form submission
        return false;
    }
    if (spaceRegex.test(awsAccountId)) {
        alert("AWS ACCOUNT ID should not contain spaces.");
        event.preventDefault(); // Prevent form submission
        return false;
    }

    // If all checks pass, allow the form to be submitted
    return true;
}
