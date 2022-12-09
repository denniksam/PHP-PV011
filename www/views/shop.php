<h2>Магазин</h2>
<form>
    <input type="text" name="name" placeholder="Название" /><br/>
    <textarea name="descr"  placeholder="Описание" ></textarea>
    <input type="number" name="price" placeholder="Цена" /><br/>
    <input type="number" name="discount" placeholder="Скидка" /><br/>
    <input type="file" name="image"  /><br/>
    <button>Добавить</button>
</form>
<?php

/*
Д.З. Создать контроллер shop_controller, реализовать прием данных формы
добавления товара, обработать сохранить файл-картинку, подготовить
(и выполнить) запрос на добавление данных в БД.

CREATE TABLE Products (
    `id`        CHAR(36)       NOT NULL   PRIMARY KEY     COMMENT 'UUID',
    `id_grp`    CHAR(36)           NULL                   COMMENT 'Group ID',
    `name`      VARCHAR(128)   NOT NULL,                  
    `descr`     TEXT           NOT NULL                   COMMENT 'Product description',
    `price`     DECIMAL(10,2)  NOT NULL,                  
    `discount`  TINYINT            NULL                   COMMENT 'Product discount in percent',
    `image`     VARCHAR(64)    NOT NULL                   COMMENT 'Product image filename',
    `rating`    DECIMAL(2,1)       NULL                   COMMENT '0..5 star-rating ',
    `votes`     INT            DEFAULT 0                  COMMENT 'Rating votes count',
    `add_dt`    DATETIME       DEFAULT CURRENT_TIMESTAMP  COMMENT 'Adding moment'
) ENGINE = InnoDB, DEFAULT CHARSET UTF8
*/
