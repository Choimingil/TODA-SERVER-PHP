# prod 서버 배포
# route 서버에서 전달받은 요청 처리
# 도커의 경우 명령어마다 레이어가 만들어지기 때문에 명령어 수를 줄이는게 중요
# && \ 로 연장한 명령 사이에 주석 들어가 있을 경우 empty continuation line warning 발생

# 이미지 세팅
# FROM을 여러 개 실행하면 각각의 FROM 이전에 실행된 명령은 초기화
FROM ubuntu:20.04
MAINTAINER Gale <cmg4739@gmail.com>

# 환경 변수 세팅
# 이미지 빌드 시에만 사용하므로 ENV 대신 ARG 사용
# 빌드 시 --no-cache 옵션이 없으면 캐시된 예전 버전의 파일을 클론함
# docker build -t toda:test --build-arg arg=arg .
ARG 	JWT_SECRET_KEY \
	SERVER_URL \
	DB_NAME \
	DB_HOST \
	DB_USER \
	DB_PW \
	FCM_ALARM_SERVER \
	REDIS_HOST

#1. 기본 세팅 & 구성요소 설치
WORKDIR /var/www
RUN mkdir api && \
apt-get update && \
apt install software-properties-common -y && \
add-apt-repository ppa:ondrej/php -y && \
apt-get install nginx -y && \
apt-get install \
	php8.0-common \
	php8.0-cli \
	php8.0-fpm \
	php8.0-cgi \
	php8.0-curl \
	php8.0-mysql \
	php8.0-opcache \
	php8.0-mbstring \
	php8.0-redis -y

#2. 패키지 COPY
COPY . /var/www/api

#3. env 수정 & 패키지 권한 부여
# sed -i 's(구문자)(바꿀 단어)(구분자)(대체단어)(구분자)g' 파일명
# 로그 폴더 생성 위해 쓰기 권한까지 부여
# 참고 : WORKDIR에서 ARG 참조 시 ${env} 형식으로 참조
WORKDIR /var/www/api
RUN	sed -i 's%JWT_SECRET_KEY_SAMPLE%'$JWT_SECRET_KEY'%g' env.php && \
	sed -i 's%SERVER_URL_SAMPLE%'$SERVER_URL'%g' env.php && \
	sed -i 's%DB_NAME_SAMPLE%'$DB_NAME'%g' env.php && \
	sed -i 's%DB_HOST_SAMPLE%'$DB_HOST'%g' env.php && \
	sed -i 's%DB_USER_SAMPLE%'$DB_USER'%g' env.php && \
	sed -i 's%DB_PW_SAMPLE%'$DB_PW'%g' env.php && \
	sed -i 's%FCM_ALARM_SERVER_SAMPLE%'$FCM_ALARM_SERVER'%g' env.php && \
	sed -i 's%REDIS_HOST_SAMPLE%'$REDIS_HOST'%g' env.php && \
	chmod -R 777 .

#4. nginx 수정
WORKDIR /etc/nginx/sites-available
RUN	sed -i 's%root /var/www/html;%root /var/www/api;%g' default && \
	sed -i 's%server_name _;%server_name localhost;%g' default && \
	sed -i 's%index index.html%index index.php index.html%g' default && \
	sed -i 's%try_files $uri $uri/ =404;%try_files $uri $uri/ /index.php?$query_string;%g' default && \
	sed -i 's%#location ~ \\%location ~ \\%g' default && \
	sed -i 's%#	include snippets/fastcgi-php.conf;%	include snippets/fastcgi-php.conf;%g' default && \
	sed -i 's%#	fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;%	fastcgi_pass unix:/run/php/php8.0-fpm.sock;%g' default && \
	sed -i 's%#	fastcgi_pass 127.0.0.1:9000;%}%g' default

#5. php 수정
WORKDIR /etc/php/8.0/fpm
RUN	sed -i 's%;date.timezone =%date.timezone = Asia/Seoul%g' php.ini && \
	sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' php.ini && \
	sed -i 's/session.cookie_httponly =/session.cookie_httponly = 1/g' php.ini && \
	sed -i 's/;session.cookie_secure =/session.cookie_secure = 1/g' php.ini && \
	sed -i 's/memory_limit = 128M/memory_limit = 256M/g' php.ini && \
	sed -i 's/post_max_size = 8M/post_max_size = 56M/g' php.ini && \
	sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 1024M/g' php.ini && \
	sed -i 's/max_file_uploads = 20/max_file_uploads = 50/g' php.ini && \
	sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=128/g' php.ini && \
	sed -i 's/;opcache.interned_strings_buffer=8/opcache.interned_strings_buffer=8/g' php.ini && \
	sed -i 's/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=50000/g' php.ini && \
	sed -i 's/;opcache.revalidate_freq=2/opcache.revalidate_freq=60/g' php.ini && \
	sed -i 's/;opcache.enable_cli=0/opcache.enable_cli=1/g' php.ini && \
	sed -i 's/;opcache.enable=1/opcache.enable=1 opcache.jit=tracing opcache.jit_buffer_size=100M/g' php.ini

#6. nginx & php 실행
# 내부에 설치한 모듈은 설정 파일을 직접 실행시켜야 정상적으로 동작
# CMD, ENTRYPOINT의 경우 Dockerfile 내에서 단 한번만 실행
# nginx 서버를 foreground로 돌리지 않으면 컨테이너를 background로 실행해도 컨테이너 안의 서버가 실행이 안된 상태이기 때문에 daemon off로 foreground로 계속 실행 중인 상황으로 만들기
#CMD service php8.0-fpm start && nginx -g "daemon off;"
ENTRYPOINT service php8.0-fpm start && nginx -g "daemon off;"
