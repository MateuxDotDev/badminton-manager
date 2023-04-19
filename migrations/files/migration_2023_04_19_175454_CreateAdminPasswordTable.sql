CREATE TABLE IF NOT EXISTS admin_passwords (
    password_hash TEXT NOT NULL,
    password_salt TEXT NOT NULL
);

-- Commands should be split by --
--

CREATE TABLE IF NOT EXISTS tournament (
    id    SERIAL PRIMARY KEY,
    name  TEXT NOT NULL,
    deadline DATE NOT NULl
);