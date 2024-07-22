FROM node:20-alpine AS assets-build

WORKDIR /var/www/html

COPY . /var/www/html/

RUN npm ci
RUN npm run build

# Use an official Nginx runtime as a parent image
FROM nginx:alpine

# Remove default configuration file
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom configuration file from the current directory
COPY ./resources/ops/nginx/default.conf /etc/nginx/conf.d

# Copy existing application directory contents
COPY . /var/www

COPY --from=assets-build /var/www/html/public /var/www

# Expose port 80
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]