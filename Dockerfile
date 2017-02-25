FROM romeoz/docker-apache-php:7.1
RUN apt-get update && \
	apt-get install -y git && \
	apt-get clean
ADD composer.* /var/www/
RUN cd ../; composer --no-ansi -nv install
##ENTRYPOINT /sbin/entrypoint.sh