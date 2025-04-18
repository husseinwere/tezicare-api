FROM php:8.2-fpm

WORKDIR /var/www/tezicare-api.tezi.co.ke

# Install Git v2.35.1 to avoid safe.directory restriction
RUN apt-get update && \
    apt-get install -y wget build-essential libssl-dev libcurl4-gnutls-dev libexpat1-dev gettext unzip && \
    cd /usr/src && \
    wget https://mirrors.edge.kernel.org/pub/software/scm/git/git-2.35.1.tar.gz && \
    tar -xf git-2.35.1.tar.gz && \
    cd git-2.35.1 && \
    make prefix=/usr/local all && \
    make prefix=/usr/local install && \
    cd .. && rm -rf git-2.35.1 git-2.35.1.tar.gz && \
    apt-get purge -y wget && apt-get autoremove -y && apt-get clean
