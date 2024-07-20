# Use an official Nginx runtime as a parent image
FROM nginx:alpine

# Remove default configuration file
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom configuration file from the current directory
COPY ./resources/ops/nginx/nginx.conf /etc/nginx/conf.d

# Copy existing application directory contents
COPY . /var/www/public

# Change current user to nginx
USER nginx

# Expose port 80
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]