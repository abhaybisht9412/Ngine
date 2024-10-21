#!/bin/bash

# Check if the correct number of arguments are provided
if [ "$#" -ne 4 ]; then
    echo "Usage: $0 <aws_access_key> <aws_secret_key> <aws_account_id> <aws_region>"
    exit 1
fi

AWS_ACCESS_KEY=$1
AWS_SECRET_KEY=$2
AWS_ACCOUNT_ID=$3
AWS_REGION=$4

# Set the AWS credentials in environment variables
export AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY
export AWS_SECRET_ACCESS_KEY=$AWS_SECRET_KEY
export AWS_DEFAULT_REGION=$AWS_REGION

# Use AWS CLI to verify the credentials by listing IAM users
if aws iam list-users >/dev/null 2>&1; then
    echo "Valid Credentials"
else
    echo "Invalid Credentials"
fi

