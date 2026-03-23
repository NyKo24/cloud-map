FROM bref/arm-php-84-fpm-dev

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/task

RUN update-ca-trust

RUN yum -y install deltarpm && \
    yum -y update && \
    yum -y install git tzdata php-intl unzip && \
    yum clean all

RUN ln -sf /usr/share/zoneinfo/Europe/Paris /etc/localtime && \
    echo "Europe/Paris" > /etc/timezone
