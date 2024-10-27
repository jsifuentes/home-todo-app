# Use an official PHP runtime as a parent image
FROM php:8.2-cli

# Set the working directory in the container
WORKDIR /var/www/html

# Copy the current directory contents into the container
COPY src/ .

# Expose port 8080 to the outside world
EXPOSE 8080

# Command to run the PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080", "-t", "web"]
