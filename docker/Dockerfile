FROM wordpress:latest

# Install WP-CLI
RUN curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp \
    && chmod +x /usr/local/bin/wp

# Install MySQL client (needed for WP-CLI db commands)
RUN apt-get update && apt-get install -y --no-install-recommends \
    default-mysql-client \
    less \
    && rm -rf /var/lib/apt/lists/*

# Copy setup script
COPY docker/setup-wordpress.sh /usr/local/bin/setup-wordpress.sh
RUN chmod +x /usr/local/bin/setup-wordpress.sh
