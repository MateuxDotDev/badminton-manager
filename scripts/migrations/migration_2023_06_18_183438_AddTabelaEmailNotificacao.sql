CREATE TABLE IF NOT EXISTS email_notificacao (
    id             UUID PRIMARY KEY NOT NULL DEFAULT gen_random_uuid(),
    conteudo       TEXT NOT NULL,
    assunto        VARCHAR(255) NOT NULL,
    destinatario   VARCHAR(255) NOT NULL,
    email_destino  VARCHAR(255) NOT NULL,
    alt_conteudo   TEXT,
    criado_em      TIMESTAMP NOT NULL DEFAULT now(),
    notificacao_id INT NOT NULL,
    FOREIGN KEY (notificacao_id) REFERENCES notificacao (id)
)

