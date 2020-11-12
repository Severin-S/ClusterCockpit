FROM php:7.4-apache
RUN apt-get update && \
	apt-get install -y unzip libldap2-dev && \
	docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && \
	docker-php-ext-install ldap && \
	\
	docker-php-ext-install mysqli && \
#	docker-php-ext-enable mysqli && \
	\
	docker-php-ext-install pdo_mysql 
#	docker-php-ext-enable pdo_mysql

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

RUN echo "memory_limit=1G" >> /usr/local/etc/php/conf.d/999-memory_limit.ini

COPY ClusterCockpit /var/www/ClusterCockpit
RUN cd /var/www/ClusterCockpit && \
        printf "DATABASE_URL=\"\"" > .env && \
        #printf "DATABASE_URL=\"\"\nINFLUXDB_URL=\"\"" > .env && \
	cp composer.json composer.json_ && \
	sed -i 's/"cache:clear": "symfony-cmd",//g' composer.json && \
	composer install && \
	mv composer.json_ composer.json && \
        echo "" > .env

COPY start_server.sh /var/www/ClusterCockpit
WORKDIR /var/www/ClusterCockpit
CMD ["/var/www/ClusterCockpit/start_server.sh"]
