DO $$
    BEGIN
        IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'sexo') THEN
            CREATE TYPE sexo AS ENUM ('M', 'F');
        END IF;
    END
$$;

--

CREATE TABLE IF NOT EXISTS atleta (
    id                       SERIAL PRIMARY KEY,
    id_tecnico               INTEGER NOT NULL,
    nome_completo            VARCHAR(255) NOT NULL,
    sexo                     SEXO NOT NULL,
    data_nascimento          DATE NOT NULL,
    informacoes_adicionais   TEXT,
    caminho_foto             VARCHAR(255),
    criado_em                TIMESTAMP NOT NULL DEFAULT NOW(),
    alterado_em              TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (id_tecnico) REFERENCES tecnico (id)
)
