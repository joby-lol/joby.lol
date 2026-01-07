# Set up image to match dev environment
FROM nginx:alpine

# Copy only what is necessary for production
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY html/ /usr/share/nginx/html/
COPY error/ /usr/share/nginx/error/