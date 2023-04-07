create table if not exists senhas_administrador (
  hash_senha text
);

-- os comandos devem ser separados por uma linha com somente --
--
create table if not exists competicao (
  id    serial primary key,
  nome  text not null,
  prazo date not null
);