# Setting Up AWS S3 Storage for Your Laravel Application

This guide will walk you through setting up AWS S3 to store and serve media files for your marketplace application.

## 1. AWS S3 Setup

### Create an IAM User with S3 Access

1. Log in to the AWS Management Console
2. Navigate to IAM (Identity and Access Management)
3. Click on "Users" then "Add user"
4. Enter a username like "marketplace-app"
5. Select "Access key - Programmatic access"
6. Click "Next: Permissions"
7. Click "Attach existing policies directly"
8. Search for and select "AmazonS3FullAccess" (for production, use more restrictive permissions)
9. Click through to create the user
10. **Important:** Save the Access Key ID and Secret Access Key - you'll need these for your application

### Configure Your S3 Bucket

1. Navigate to the S3 service in AWS Console
2. Click "Create bucket"
3. Enter "marketplace-bucket-demo" as your bucket name
4. Select your preferred region (make sure you use the same region in your app config)
5. Uncheck "Block all public access" (be careful with this in production)
6. Click "Create bucket"

### Configure Bucket for Public Access

For your media files to be publicly accessible:

1. Select your bucket in the S3 console
2. Go to the "Permissions" tab
3. Under "Block public access", click "Edit" and uncheck all options (Be cautious in production)
4. Under "Bucket policy", add a policy that allows public read access:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::marketplace-bucket-demo/*"
        }
    ]
}
```

5. Configure CORS (Cross-Origin Resource Sharing) to allow your domain to access the bucket:

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "HEAD", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["*"],
        "ExposeHeaders": ["ETag"],
        "MaxAgeSeconds": 3000
    }
]
```

## 2. Application Setup

### Configure Your Local Environment

Update your `.env` file with your AWS credentials:

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-actual-access-key
AWS_SECRET_ACCESS_KEY=your-actual-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=marketplace-bucket-demo
AWS_URL=https://marketplace-bucket-demo.s3.amazonaws.com
```

### Configure Railway Environment

1. Log in to your Railway dashboard
2. Select your marketplace-backend project
3. Go to "Variables" tab
4. Add the following variables:
   - `FILESYSTEM_DISK=s3`
   - `AWS_ACCESS_KEY_ID=your-actual-access-key`
   - `AWS_SECRET_ACCESS_KEY=your-actual-secret-key`
   - `AWS_DEFAULT_REGION=us-east-1`
   - `AWS_BUCKET=marketplace-bucket-demo`
   - `AWS_URL=https://marketplace-bucket-demo.s3.amazonaws.com`

## 3. Test Your S3 Configuration

You can run our S3 CORS configuration script to ensure proper setup:

```bash
php configure-s3-cors.php
```

Then upload a test file through your application and verify it's accessible.

## 4. Troubleshooting

If you're having issues accessing files:

1. Check that your bucket policy allows public read access
2. Ensure CORS is properly configured to allow your domain
3. Verify the AWS credentials are correct in your environment variables
4. Check Laravel logs for any S3 connection errors

## Security Considerations

For production environments:

1. Use more restrictive IAM permissions than `AmazonS3FullAccess`
2. Consider using CloudFront as a CDN in front of your S3 bucket
3. Update your CORS configuration to only allow specific domains
4. Implement signed URLs for more controlled access to your media files
5. Consider enabling bucket versioning for file history 