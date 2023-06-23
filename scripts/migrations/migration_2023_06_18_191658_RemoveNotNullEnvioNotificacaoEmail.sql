ALTER TABLE envio_email_notificacao ALTER COLUMN enviado_em DROP NOT NULL;

--

ALTER TABLE envio_email_notificacao ALTER COLUMN enviado_em DROP DEFAULT;
