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

DROP TABLE IF EXISTS criticas CASCADE;

CREATE TABLE criticas
(
    id            BIGSERIAL         PRIMARY KEY
  , texto         TEXT              NOT NULL
  , created_at    TIMESTAMP(0)      NOT NULL
                                    DEFAULT CURRENT_TIMESTAMP
  , usuario_id    BIGINT            NOT NULL
                                    REFERENCES usuarios(id)
  , padre_id      BIGINT            REFERENCES comentarios(id)
  , producto_id   BIGINT            NOT NULL
                                    REFERENCES eventos(id)
);
