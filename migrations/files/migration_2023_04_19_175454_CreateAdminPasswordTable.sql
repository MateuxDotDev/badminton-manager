CREATE TABLE IF NOT EXISTS admin (
    "user" TEXT PRIMARY KEY,
    hash_senha TEXT NOT NULL,
    salt_senha TEXT NOT NULL
);

-- Commands should be split by --
--

CREATE TABLE IF NOT EXISTS competicao (
    id    SERIAL PRIMARY KEY,
    nome  TEXT NOT NULL,
    prazo DATE NOT NULl
);