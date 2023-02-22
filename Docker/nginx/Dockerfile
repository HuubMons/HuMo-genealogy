FROM nginx:latest
RUN echo 'server {' > /etc/nginx/conf.d/default.conf ; \
    echo '  index index.php index.html;' >> /etc/nginx/conf.d/default.conf ; \
    echo '  server_name _;' >> /etc/nginx/conf.d/default.conf ; \
    echo '  error_log /proc/self/fd/2;' >> /etc/nginx/conf.d/default.conf ; \
    echo '  access_log /proc/self/fd/1;' >> /etc/nginx/conf.d/default.conf ; \
    echo '  root /var/www/html;' >> /etc/nginx/conf.d/default.conf ; \
    echo '  client_max_body_size 100M;' >> /etc/nginx/conf.d/default.conf ; \
    echo '  location ~ \.php$ {' >> /etc/nginx/conf.d/default.conf ; \
    echo '    try_files $uri =404;' >> /etc/nginx/conf.d/default.conf ; \
    echo '    fastcgi_split_path_info ^(.+\.php)(/.+)$;' >> /etc/nginx/conf.d/default.conf ; \
    echo '    fastcgi_pass fpm:9000;' >> /etc/nginx/conf.d/default.conf ; \
    echo '    fastcgi_index index.php;' >> /etc/nginx/conf.d/default.conf ; \
    echo '    include fastcgi_params;' >> /etc/nginx/conf.d/default.conf ; \
    echo '    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/conf.d/default.conf ; \
    echo '    fastcgi_param PATH_INFO $fastcgi_path_info;' >> /etc/nginx/conf.d/default.conf ; \
    echo '  }' >> /etc/nginx/conf.d/default.conf ; \
    echo '}' >> /etc/nginx/conf.d/default.conf

ADD ./ /var/www/html