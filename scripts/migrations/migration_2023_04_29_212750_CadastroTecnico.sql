CREATE TABLE clube (
    id        SERIAL PRIMARY KEY,
    nome      TEXT NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT NOW()
);
--
CREATE TABLE tecnico (
    id            SERIAL PRIMARY KEY,
    clube_id      BIGINT REFERENCES clube(id),
    nome_completo TEXT NOT NULL,
    email         TEXT NOT NULL UNIQUE,
    hash_senha    TEXT NULL,
    salt_senha    TEXT NULL,
    informacoes   TEXT NOT NULL DEFAULT '',
    criado_em     TIMESTAMP NOT NULL DEFAULT NOW(),
    alterado_em   TIMESTAMP NOT NULL DEFAULT NOW()
);