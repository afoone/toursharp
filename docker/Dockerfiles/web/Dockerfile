FROM irontec/debian:jessie-php7.0.11-apache-tini

ADD php-docker.ini /usr/local/etc/php/conf.d/php-docker.ini

RUN apt-get update \
		&& apt-get install -y \
			libicu-dev \
			libmcrypt-dev \
			libpng12-dev \
			unixodbc-dev \
			libxml2-dev \
			libxslt1-dev \
			zip \
			git \
			mysql-client \
			sudo \
			zlib1g-dev \
			freetds-dev

RUN a2enmod rewrite deflate ssl \
	&& apt-get install -y libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install \
            iconv \
            mbstring \
            intl \
            mcrypt \
            gd \
            mysqli \
            pdo_mysql \
            xsl \
            soap \
            sockets \
            zip \
            pcntl \
	&& docker-php-ext-enable \
            opcache

# Clean repository
RUN apt-get autoclean -y \
    && docker-php-source delete \
    && rm -rf /var/lib/apt/lists/*

###############################################
#            Installing Composer              #
###############################################
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer
    
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
#RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === file_get_contents('https://composer.github.io/installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --install-dir=/bin/ --filename=composer
   #install globally
   #&& mv composer.phar /usr/local/bin/composer
RUN php -r "unlink('composer-setup.php');"


###############################################
#                Docker User                  #
###############################################
RUN groupadd -r docker -g 1000 && \
useradd -u 1000 -r -g docker -d /var/www/html -s /bin/bash -c "Docker user" docker && \
usermod -a -G www-data  docker && \
chown -R docker:www-data /var/www/html && \
echo "docker:docker" | chpasswd && \
adduser docker sudo && \
echo "docker ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/docker

###############################################
#                Install SASS                 #
###############################################
# Install Node
RUN curl -sL https://deb.nodesource.com/setup_4.x | bash - \
  && apt-get install -y nodejs

USER docker
	
###############################################
#    Adding composer/vendor/bin to PATH#      #
###############################################
RUN export PATH=~/.composer/vendor/bin:$PATH

###############################################
#       Make the PATH changes permanent       #
###############################################
RUN echo 'export PATH=~/.composer/vendor/bin:$PATH'  >> ~/.bash_profile
RUN echo 'export PATH=~/.composer/vendor/bin:$PATH'  >> ~/.bashrc
RUN echo 'export PATH=~/.composer/vendor/bin:$PATH'  >> ~/.profile

USER root

COPY run.sh /run.sh
RUN chmod +x /run.sh

COPY generate-certs.sh /generate-certs.sh
RUN chmod +x /generate-certs.sh

COPY ssl_config /tmp/ssl_config

RUN /generate-certs.sh 'www.masiadetraver.com' /tmp/ssl_config

COPY 000-default.conf /etc/apache2/sites-enabled/
COPY default-ssl.conf /etc/apache2/sites-enabled/
	
USER docker