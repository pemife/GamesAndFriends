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
