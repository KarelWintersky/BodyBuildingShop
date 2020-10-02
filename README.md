# BodyBuildingShop

## Build & Deploy

Для удобного деплоя внедрен механизм сборки DEB-пакетов. 

Перед коммитом новой версии делаем апдейт чейнджлога:

`dch -v <version>`

Фиксируем версию:

`dch --release --distribution unstable/stable`

Делаем commit + push

На билд-машине (можно на этой же, в другом каталоге):

`git clone https://github.com/KarelWintersky/BodyBuildingShop.git`  или `make update`

Потом собираем пакет `make build` и заливаем его на хост. 

Как вариант, можно собирать сразу на целевой машине, находясь в `/home/<user>` 

NB: Пакет настроен так, чтобы класть сайт в `/var/www/bbs/`

## Config

Конфиг лежит в `$/kernel/config.php`

# Backups

@todo (выполняются по кронскрипту, где, как описаны, вот это всё)


--- 

## Что нужно для сборки:
```
apt install make build-essential fakeroot debhelper 
```
