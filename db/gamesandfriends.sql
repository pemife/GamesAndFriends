------------------------------
-- Archivo de base de datos --
------------------------------

DROP TABLE IF EXISTS usuarios CASCADE;

CREATE TABLE usuarios
(
    id                  BIGSERIAL       PRIMARY KEY
  , nombre              VARCHAR(32)     NOT NULL UNIQUE
                                        CONSTRAINT ck_nombre_sin_espacios
                                        CHECK (nombre NOT ILIKE '% %')
  , password                            VARCHAR(60)   NOT NULL
  , created_at          DATE            NOT NULL DEFAULT CURRENT_DATE
  , requested_at        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
  , token               VARCHAR(32)
  , email               VARCHAR(255)    NOT NULL UNIQUE
  , biografia           TEXT
  , fechanac            DATE            CHECK (fechanac < CURRENT_DATE)
);

DROP TABLE IF EXISTS plataformas CASCADE;

CREATE TABLE plataformas
(
  id          BIGSERIAL           PRIMARY KEY
  , nombre      VARCHAR(50)         UNIQUE
);

DROP TABLE IF EXISTS juegos CASCADE;

CREATE TABLE juegos
(
    id           BIGSERIAL     PRIMARY KEY
  , titulo       VARCHAR(255)  NOT NULL UNIQUE
  , descripcion  TEXT
  , fechaLan     DATE
  , dev          VARCHAR(255)  NOT NULL
  , publ         VARCHAR(255)  NOT NULL
  , cont_adul    BOOLEAN       NOT NULL
                               DEFAULT false
  , edad_minima  NUMERIC(2)    NOT NULL
  , CONSTRAINT CHK_Edad_Minima_Correcta CHECK (edad_minima=3 OR edad_minima=7 OR edad_minima=12 OR edad_minima=16 OR edad_minima=18)
);

DROP TABLE productos CASCADE;

CREATE TABLE productos
(
    id            BIGSERIAL         PRIMARY KEY
  , nombre        VARCHAR(255)      NOT NULL UNIQUE
  , descripcion   TEXT              NOT NULL
  , stock         NUMERIC(5)        NOT NULL
  , propietario_id   BIGINT         REFERENCES usuarios(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
);

DROP TABLE IF EXISTS criticas CASCADE;

CREATE TABLE criticas
(
    id            BIGSERIAL         PRIMARY KEY
  , opinion       TEXT              NOT NULL
  , created_at    DATE              NOT NULL
                                    DEFAULT CURRENT_TIMESTAMP
  , last_update   DATE              NOT NULL
                                    DEFAULT CURRENT_TIMESTAMP
  , valoracion    NUMERIC(1)        NOT NULL
                                    CHECK (valoracion >= 0)
  , usuario_id    BIGINT            NOT NULL
                                    REFERENCES usuarios(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
  , producto_id   BIGINT            REFERENCES productos(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
  , juego_id      BIGINT            REFERENCES juegos(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
  , CONSTRAINT uq_usuario_producto  UNIQUE (usuario_id, producto_id)
  , CONSTRAINT uq_usuario_juego  UNIQUE (usuario_id, juego_id)
  , CONSTRAINT ck_alternar_valores_nulos CHECK (
        (producto_id IS NOT NULL AND juego_id IS NULL)
        OR
        (producto_id IS NULL AND juego_id IS NOT NULL)
    )
);

DROP TABLE IF EXISTS posts CASCADE;

CREATE TABLE posts
(
    id            BIGSERIAL         PRIMARY KEY
  , titulo        VARCHAR(255)      NOT NULL
  , created_at    TIMESTAMPTZ(0)      NOT NULL
                                    DEFAULT CURRENT_TIMESTAMP
  , media         VARCHAR(255)
  , desarrollo    TEXT
  , juego_id      BIGINT            NOT NULL
                                    REFERENCES juegos(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , usuario_id    BIGINT            NOT NULL
                                    REFERENCES usuarios(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
);

DROP TABLE IF EXISTS comentarios CASCADE;

CREATE TABLE comentarios
(
    id              BIGSERIAL         PRIMARY KEY
  , created_at      TIMESTAMPTZ(0)      NOT NULL
                                      DEFAULT CURRENT_TIMESTAMP
  , texto           TEXT              NOT NULL
  , usuario_id      BIGINT            NOT NULL
                                      REFERENCES usuarios(id)
                                      ON DELETE CASCADE
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

DROP TABLE IF EXISTS copias CASCADE;

CREATE TABLE copias
(
    id            BIGSERIAL         PRIMARY KEY
  , juego_id      BIGINT            NOT NULL
                                    REFERENCES juegos(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , propietario_id   BIGINT         REFERENCES usuarios(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
  , clave         VARCHAR(17)       CONSTRAINT ck_patron_clave
                                    CHECK (clave LIKE '_____-_____-_____')
  , plataforma_id BIGINT            NOT NULL
                                    REFERENCES plataformas(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
  , CONSTRAINT uq_clave_plataforma UNIQUE (clave, plataforma_id)
 );

DROP TABLE IF EXISTS ventas CASCADE;

CREATE TABLE ventas
(
    id            BIGSERIAL         PRIMARY KEY
  , created_at    TIMESTAMPTZ(0)      NOT NULL
                                    DEFAULT CURRENT_TIMESTAMP
  , finished_at   TIMESTAMPTZ(0)
  , vendedor_id   BIGINT            NOT NULL
                                    REFERENCES usuarios(id)
                                    ON DELETE CASCADE
                                    ON UPDATE CASCADE
  , comprador_id  BIGINT            REFERENCES usuarios(id)
                                    ON DELETE CASCADE
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

ALTER TABLE usuarios
ADD COLUMN venta_solicitada     BIGINT      REFERENCES ventas(id)
                                            ON DELETE CASCADE
                                            ON UPDATE CASCADE
;

DROP TABLE IF EXISTS relaciones CASCADE;

CREATE TABLE relaciones
(
      usuario1_id    BIGINT         REFERENCES usuarios(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
    , usuario2_id    BIGINT         REFERENCES usuarios(id)
                                    ON DELETE NO ACTION
                                    ON UPDATE CASCADE
    , estado         NUMERIC(1)     NOT NULL
                                    DEFAULT 0
    , old_estado     NUMERIC(1)     DEFAULT 2
    , PRIMARY KEY(usuario1_id, usuario2_id)
    , CONSTRAINT CHK_SelfFriend CHECK (usuario1_id != usuario2_id)
    , CONSTRAINT CHK_Estado_Valido CHECK (estado=0 OR estado=1 OR estado=2 OR estado=3)
    , CONSTRAINT CHK_Old_Estado_Valido CHECK (old_estado=0 OR old_estado=1 OR old_estado=2 OR old_estado=3)
);

-- DROP SEQUENCE deseados_orden_seq CASCADE;

DROP TABLE IF EXISTS deseados CASCADE;

CREATE TABLE deseados
(
    usuario_id  BIGINT        REFERENCES usuarios(id)
                              ON DELETE NO ACTION
                              ON UPDATE CASCADE
  , juego_id    BIGINT        REFERENCES juegos(id)
                              ON DELETE NO ACTION
                              ON UPDATE CASCADE
  , orden       SMALLINT      NOT NULL
  , PRIMARY KEY(usuario_id, juego_id)
);

DROP TABLE IF EXISTS votos_posts;

CREATE TABLE votos_posts
(
    usuario_id  BIGINT      REFERENCES usuarios(id)
                            ON DELETE NO ACTION
                            ON UPDATE CASCADE
  , post_id     BIGINT      REFERENCES posts(id)
                            ON DELETE NO ACTION
                            ON UPDATE CASCADE  
  , PRIMARY KEY(usuario_id, post_id)
);

DROP TABLE IF EXISTS reportes_comentarios CASCADE;

CREATE TABLE reportes_comentarios
(
    usuario_id      BIGINT      REFERENCES usuarios(id)
                                ON DELETE NO ACTION
                                ON UPDATE CASCADE
  , comentario_id   BIGINT      REFERENCES comentarios(id)
                                ON DELETE NO ACTION
                                ON UPDATE CASCADE
  , razon       TEXT
  , PRIMARY KEY(usuario_id, comentario_id)
);

DROP TABLE IF EXISTS juegos_ignorados CASCADE;

CREATE TABLE juegos_ignorados
(
    usuario_id      BIGINT        REFERENCES usuarios(id)
                                  ON DELETE NO ACTION
                                  ON UPDATE CASCADE
  , juego_id        BIGINT        REFERENCES juegos(id)
                                  ON DELETE NO ACTION
                                  ON UPDATE CASCADE
  , PRIMARY KEY(usuario_id, juego_id)
);

--INSERTS --

INSERT INTO usuarios (nombre, password, email, fechanac)
VALUES ('admin', crypt('hnmpl', gen_salt('bf', 10)), 'gamesandfriends2@gmail.com', '1987-01-01'),
('pepe', crypt('pepe', gen_salt('bf', 10)), 'jose.millan@iesdonana.org', '1995-12-03');

INSERT INTO juegos (titulo, descripcion, fechaLan, dev, publ, cont_adul, edad_minima)
VALUES ('Rocket League', 'Futbol con coches teledirigidos equipados con un cohete. Una entrega de juego basado en fisicas con el motor Unreal Engine.', '2015-07-07', 'Psyonix LLC', 'Psyonix LLC', false, 3),
('The Binding of Isaac: Rebirth', 'Adéntrate en el sótano intentando huir de tu asesina, un juego Rogue-Like con esteticas bizarras y miles de secretos.', '2014-11-04', 'Nicalis Inc.', 'Nicalis Inc.', false, 12),
('Counter Strike: Global Offensive', 'Juego de tiros en primera persona tactico, secuela de la mitica saga counter strike.', '2012-08-21', 'Valve', 'Valve', false, 16);

INSERT INTO productos (nombre, descripcion, stock, propietario_id)
VALUES ('Funko POP de Psyco de Borderlands 3', 'De los juegos de Borderlands, llega el Funko POP de Psyco, los maniaticos al frente de los grupos hostiles en Pandora.', 5, 2);

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
VALUES (2,5),(2,6),(2,8),(2,9),(1,1),(1,2),(1,3),(1,7),(3,3),(3,7),(3,12),(3,13),(3,14);

INSERT INTO plataformas (nombre)
VALUES ('PC'),('PlayStation 4'),('Xbox One'),('Nintendo Switch');

INSERT INTO copias (juego_id, propietario_id, clave, plataforma_id)
VALUES (1, 2, 'K57F0-PV9M6-8MZ4Y', 1), (2, 2, 'IZM46-23GIN-5IPAN', 4),
(1, 2, 'KK57W-KKVQF-JMDZC', 4), (3, 2, 'SDK32-182SJ-12WKS', 1);

INSERT INTO ventas(created_at, finished_at, vendedor_id, comprador_id, producto_id, copia_id, precio)
VALUES (CURRENT_TIMESTAMP, null, 2, null, 1, null, 9000.01),
(CURRENT_TIMESTAMP, null, 2, null, null, 2, 9000.01),
(CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, 2, null, 3, 9000.01),
(CURRENT_TIMESTAMP, null, 2, null, null, 4, 9000.01);
