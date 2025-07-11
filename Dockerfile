FROM wordpress:latest

# Install required packages
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    vim \
    && rm -rf /var/lib/apt/lists/*

# Set upload limits
RUN echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Create uploads directory
RUN mkdir -p /var/www/html/wp-content/uploads && \
    chown -R www-data:www-data /var/www/html/wp-content/uploads

EXPOSE 80