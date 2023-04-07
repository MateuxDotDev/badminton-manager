-- arquivos iniciando em 0 são ignorados
-- talvez esse seria o único create table num arquivo setup.sql que é rodado logo que o container é subido
create table migrations_executadas (
  nome     text primary key,
  datahora timestamp not null default now()
);