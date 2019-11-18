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
  , poseedor_id   BIGINT            REFERENCES usuarios(id)
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

DROP TABLE IF EXISTS plataformas CASCADE;

CREATE TABLE plataformas
(
    id          BIGSERIAL           PRIMARY KEY
  , nombre      VARCHAR(50)         UNIQUE
);

DROP TABLE IF EXISTS copias CASCADE;

CREATE TABLE copias
(
    id            BIGSERIAL         PRIMARY KEY
  , juego_id      BIGINT            NOT NULL
                                    REFERENCES juegos(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , poseedor_id   BIGINT            REFERENCES usuarios(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
  , clave         VARCHAR(17)
  , plataforma_id BIGINT            NOT NULL
                                    REFERENCES plataformas(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
 );

DROP TABLE IF EXISTS ventas CASCADE;

CREATE TABLE ventas
(
    id            BIGSERIAL         PRIMARY KEY
  , created_at    TIMESTAMP(0)      NOT NULL
                                    DEFAULT CURRENT_TIMESTAMP
  , finished_at   TIMESTAMP(0)
  , vendedor_id   BIGINT            NOT NULL
                                    REFERENCES usuarios(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , comprador_id  BIGINT            REFERENCES usuarios(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , producto_id   BIGINT            UNIQUE REFERENCES productos(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , copia_id      BIGINT            UNIQUE REFERENCES copias(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , precio        NUMERIC(6,2)      NOT NULL
  , CONSTRAINT ck_alternar_valores_nulos CHECK (
        (producto_id IS NOT NULL AND copia_id IS NULL)
        OR
        (producto_id IS NULL AND copia_id IS NOT NULL)
    )
);


--INSERTS --

INSERT INTO usuarios (nombre, password, email)
VALUES ('admin', crypt('hnmpl', gen_salt('bf', 10)), 'admin@aculturese.com'),
('pepe', crypt('pepe', gen_salt('bf', 10)), 'jose.millan@iesdonana.org');

INSERT INTO juegos (titulo, descripcion, fechaLan, dev)
VALUES ('Rocket League', 'Futbol con coches teledirigidos equipados con un cohete. Una entrega de juego basado en fisicas con el motor Unreal Engine.', '2015-07-07', 'Psyonix Inc.'),
('The Binding of Isaac: Rebirth', 'Adéntrate en el sótano intentando huir de tu asesina, un juego Rogue-Like con esteticas bizarras y miles de secretos.', '2014-11-04', 'Nicalis Inc.'),
('Counter Strike: Global Offensive', 'Juego de tiros en primera persona tactico, secuela de la mitica saga counter strike.', '2012-08-21', 'Valve Inc');

INSERT INTO productos (nombre, descripcion, precio, stock, poseedor_id)
VALUES ('Funko POP de psyco de Borderlands 3', 'De los juegos de Borderlands, llega el Funko POP de Psyco, los maniaticos al frente de los grupos hostiles en Pandora.', 19.99, 5, 2);

INSERT INTO criticas (opinion, created_at, valoracion, usuario_id, producto_id)
VALUES ('Pues a mi los Funkos no me gustan, pero tener un psyco en mi cuarto me mola', CURRENT_TIMESTAMP, 5, 2, 1);

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

INSERT INTO plataformas (nombre)
VALUES ('PC'),('PlayStation 4'),('Xbox One'),('Nintendo Switch');

INSERT INTO copias (juego_id, poseedor_id, clave, plataforma_id)
VALUES (1, 2, 'K57F0-PV9M6-8MZ4Y', 1), (2, 2, 'IZM46-23GIN-5IPAN', 4),
(1, 2, 'KK57W-KKVQF-JMDZC', 4), (3, 2, 'SDK32-182SJ-12WKS', 1);

INSERT INTO ventas(created_at, finished_at, vendedor_id, comprador_id, producto_id, copia_id, precio)
VALUES (CURRENT_TIMESTAMP, null, 1, null, 1, null, 9000.01),
(CURRENT_TIMESTAMP, null, 2, null, null, 2, 9000.01),
(CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, 2, null, 3, 9000.01),
(CURRENT_TIMESTAMP, null, 2, null, null, 4, 9000.01);
