------------------------------
-- Archivo de base de datos --
------------------------------

DROP TABLE IF EXISTS usuarios CASCADE;

CREATE TABLE usuarios
(
    id          BIGSERIAL     PRIMARY KEY
  , nombre      VARCHAR(32)   NOT NULL UNIQUE
                              CONSTRAINT ck_nombre_sin_espacios
                              CHECK (nombre NOT ILIKE '% %')
  , password    VARCHAR(60)   NOT NULL
  , created_at  DATE          NOT NULL DEFAULT CURRENT_DATE
  , token       VARCHAR(32)
  , email       VARCHAR(255)  NOT NULL UNIQUE
  , biografia   TEXT
  , fechaNac    DATE
);

DROP TABLE IF EXISTS juegos CASCADE;

CREATE TABLE juegos
(
    id            BIGSERIAL     PRIMARY KEY
  , titulo        VARCHAR(255)  NOT NULL UNIQUE
  , descripcion   TEXT
  , fechaLan      DATE
  , dev           VARCHAR(255)  NOT NULL UNIQUE
);

DROP TABLE productos CASCADE;

CREATE TABLE productos
(
    id            BIGSERIAL         PRIMARY KEY
  , nombre        VARCHAR(255)      NOT NULL UNIQUE
  , descripcion   TEXT              NOT NULL
  , precio        NUMERIC(6,2)
  , stock         NUMERIC(5)        NOT NULL
  , juego_id      BIGINT            REFERENCES juegos(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
);

DROP TABLE IF EXISTS criticas CASCADE;

CREATE TABLE criticas
(
    id            BIGSERIAL         PRIMARY KEY
  , opinion       TEXT              NOT NULL
  , created_at    TIMESTAMP(0)      NOT NULL
                                    DEFAULT CURRENT_TIMESTAMP
  , valoracion    NUMERIC(1)        NOT NULL
                                    CHECK (valoracion > 0)
  , usuario_id    BIGINT            NOT NULL
                                    REFERENCES usuarios(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , producto_id   BIGINT            NOT NULL
                                    REFERENCES productos(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
);

DROP TABLE IF EXISTS posts CASCADE;

CREATE TABLE posts
(
    id            BIGSERIAL         PRIMARY KEY
  , titulo        VARCHAR(255)      NOT NULL
  , created_at    TIMESTAMP(0)      NOT NULL
                                    DEFAULT CURRENT_TIMESTAMP
  , media         VARCHAR(255)
  , desarrollo    TEXT
  , juego_id      BIGINT            NOT NULL
                                    REFERENCES juegos(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , usuario_id    BIGINT            NOT NULL
                                    REFERENCES usuarios(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
);

DROP TABLE IF EXISTS comentarios CASCADE;

CREATE TABLE comentarios
(
    id              BIGSERIAL         PRIMARY KEY
  , created_at      TIMESTAMP(0)      NOT NULL
                                      DEFAULT CURRENT_TIMESTAMP
  , texto           TEXT              NOT NULL
  , usuario_id      BIGINT            NOT NULL
                                      REFERENCES usuarios(id)
                                      ON DELETE NO ACTION
                                      ON UPDATE CASCADE
  , post_id         BIGINT            NOT NULL
                                      REFERENCES posts(id)
                                      ON DELETE CASCADE
                                      ON UPDATE CASCADE
);

DROP TABLE IF EXISTS etiquetas CASCADE;

CREATE TABLE etiquetas
(
    id              BIGSERIAL         PRIMARY KEY
  , nombre          VARCHAR(20)       NOT NULL UNIQUE
);

DROP TABLE IF EXISTS usuarios_etiquetas CASCADE;

CREATE TABLE usuarios_etiquetas
(
    id              BIGSERIAL         PRIMARY KEY
  , usuario_id      BIGINT            NOT NULL
                                      REFERENCES usuarios(id)
                                      ON DELETE CASCADE
                                      ON UPDATE CASCADE
  , etiqueta_id     BIGINT            NOT NULL
                                      REFERENCES etiquetas(id)
                                      ON DELETE CASCADE
                                      ON UPDATE CASCADE
);

DROP TABLE IF EXISTS juegos_etiquetas CASCADE;

CREATE TABLE juegos_etiquetas
(
    id               BIGSERIAL         PRIMARY KEY
  , juego_id         BIGINT            NOT NULL
                                       REFERENCES juegos(id)
                                       ON DELETE CASCADE
                                       ON UPDATE CASCADE
  , etiqueta_id      BIGINT            NOT NULL
                                       REFERENCES etiquetas(id)
                                       ON DELETE CASCADE
                                       ON UPDATE CASCADE
);


--INSERTS --

INSERT INTO usuarios (nombre, password, email)
VALUES ('admin', crypt('hnmpl', gen_salt('bf', 10)), 'admin@aculturese.com'),
('pepe', crypt('pepe', gen_salt('bf', 10)), 'jose.millan@iesdonana.org');

INSERT INTO juegos (titulo, descripcion, fechaLan, dev)
VALUES ('Rocket League', 'Futbol con coches teledirigidos equipados con un cohete. Una entrega de juego basado en fisicas con el motor Unreal Engine.', '2015-07-07', 'Psyonix Inc.'),
('The Binding of Isaac: Rebirth', 'Adéntrate en el sótano intentando huir de tu asesina, un juego Rogue-Like con esteticas bizarras y miles de secretos.', '2014-11-04', 'Nicalis Inc.');

INSERT INTO productos (nombre, descripcion, precio, stock, juego_id)
VALUES ('Rocket League', 'Futbol con coches teledirigidos equipados con un cohete. Una entrega de juego basado en fisicas con el motor Unreal Engine.', 19.99, 9001, 1),
('The Binding of Isaac: Rebirth', 'Adéntrate en el sótano intentando huir de tu asesina, un juego Rogue-Like con esteticas bizarras y miles de secretos.', 14.99, 9001, 2),
('Funko POP de psyco de Borderlands 3', 'De los juegos de Borderlands, llega el Funko POP de Psyco, los maniaticos al frente de los grupos hostiles en Pandora.', 19.99, 5, null);

INSERT INTO criticas (opinion, created_at, valoracion, usuario_id, producto_id)
VALUES ('Pues es un juegazo, me encanta', CURRENT_TIMESTAMP, 9, 2, 1),
('Es algo turbio, pero me gusta, los enemigos son muy raros, tiene su encanto', CURRENT_TIMESTAMP, 9, 2, 2),
('Pues a mi los Funkos no me gustan, pero tener un psyco en mi cuarto me mola', CURRENT_TIMESTAMP, 5, 2, 3);

INSERT INTO posts (titulo, created_at, desarrollo, juego_id, usuario_id)
VALUES ('Primer post', CURRENT_TIMESTAMP, 'Cuando empece el proyecto hice este post, para crear una prueba y aqui se quedó la prueba por ahora, ya la cambiare, pero por ahora, asi se mantendrá.', 2, 2);

INSERT INTO comentarios (created_at, texto, usuario_id, post_id)
VALUES (CURRENT_TIMESTAMP, 'Pues me gusta tu post, no lo cambies', 1, 1),
(CURRENT_TIMESTAMP, 'jejeje, me he comentado a mi mismo', 2, 1);

INSERT INTO etiquetas (nombre)
VALUES ('Deportes'), ('Carreras'), ('Competitivo'), ('Casual'), ('Adictivo'),
('Rogue-like'), ('Multijugador'), ('Indie'), ('Dificil'), ('Un jugador'),
('RPG'), ('Shooter'), ('FPS'), ('Accion');

INSERT INTO usuarios_etiquetas (usuario_id, etiqueta_id)
VALUES (1,1), (1,3), (1,4),(1,6);

INSERT INTO juegos_etiquetas (juego_id, etiqueta_id)
VALUES (2,5), (2,6), (2,8), (2,9), (1,1), (1,2), (1,3), (1,7);
