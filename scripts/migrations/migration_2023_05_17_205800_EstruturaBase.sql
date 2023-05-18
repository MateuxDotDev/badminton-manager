drop table if exists atleta;

--
create table if not exists atleta (
    id              serial primary key,
    tecnico_id      bigint references tecnico(id),
    sexo sexo       not null,
    data_nascimento date not null,
    nome_completo   text not null,
    informacoes     text not null default '',
    path_foto       text null,
    criado_em       timestamp not null default now(),
    alterado_em     timestamp not null default now(),
    /* necessário para a última fk na solicitacao_dupla_pendente */
    unique(id, sexo)
);

--
/* sub15, sub17 etc. */
create table if not exists categoria (
    descricao text primary key,
    /* a partir dessa idade não pode mais jogar
       (começa a partir do ano que o atleta faz aniversário
       p.ex. mesmo que a competição ocorra quando ele ainda tiver 16, se ele fizer 17 no mesmo ano, não pode mais na sub17) */
    idade_limite integer,
    check (idade_limite >= 0)
);

--
create table if not exists atleta_competicao (
    atleta_id     bigint references atleta(id),
    competicao_id bigint references competicao(id),
    informacoes   text not null default '',
    criado_em     timestamp not null default now(),
    alterado_em   timestamp not null default now(),
    primary key (atleta_id, competicao_id)
);

--
/* categorias em que o atleta está disposto a jogar */
create table if not exists atleta_competicao_categoria (
    atleta_id     bigint,
    competicao_id bigint,
    categoria     text references categoria(descricao),
    primary key (atleta_id, competicao_id, categoria),
    foreign key (atleta_id, competicao_id) references atleta_competicao (atleta_id, competicao_id)
);

--
/* tipo de dupla de que o atleta precisa (sexo do outro jogador) */
create table if not exists atleta_competicao_tipo_dupla (
    atleta_id     bigint,
    competicao_id bigint,
    tipo_dupla    sexo,
    primary key (atleta_id, competicao_id, tipo_dupla),
    foreign key (atleta_id, competicao_id) references atleta_competicao (atleta_id, competicao_id)
);

--
/* uma tabela é para solicitações pendentes, essa tem várias FKs para manter integridade
   e outra é para solicitações concluídas, sem as FKs para caso o atleta saia da competição etc.
   quando solicitação é concluída (aceita/rejeitada/cancelada), é excluída da _pendente e incluida na _concluida */

create table if not exists solicitacao_dupla_pendente (
    id serial primary key,

    competicao_id     bigint references competicao(id),
    atleta_origem_id  bigint references atleta(id),
    atleta_destino_id bigint references atleta(id),
    informacoes       text not null default '',
    categoria         text references categoria(descricao),
    tipo_dupla        sexo not null,
    criado_em         timestamp not null default now(),
    alterado_em       timestamp not null default now(),

    unique (competicao_id, atleta_origem_id, atleta_destino_id, categoria),

    foreign key (atleta_origem_id, competicao_id, categoria)
    references atleta_competicao_categoria (atleta_id, competicao_id, categoria),

    foreign key (atleta_destino_id, competicao_id, categoria)
    references atleta_competicao_categoria (atleta_id, competicao_id, categoria),

    foreign key (atleta_origem_id, competicao_id, tipo_dupla)
    references atleta_competicao_tipo_dupla (atleta_id, competicao_id, tipo_dupla),

    foreign key (atleta_destino_id, tipo_dupla)
    references atleta (id, sexo)
);

--
create table if not exists solicitacao_dupla_concluida (
    /* não corresponde ao id da solicitacao_dupla_pendente original */
    id serial primary key,

    /* dados copiados da soliciacao_dupla_pendente antes da exclusão */
    competicao_id     bigint references competicao(id),
    atleta_origem_id  bigint references atleta(id),
    atleta_destino_id bigint references atleta(id),
    informacoes       text not null,
    categoria         text references categoria(descricao),
    tipo_dupla        sexo not null,
    criado_em         timestamp not null,
    alterado_em       timestamp not null,

    aceita_em    timestamp null,
    rejeitada_em timestamp null,
    cancelada_em timestamp null,
    /* rejeitada é equivalente a cancelada, mas é mais descritivo ter as duas sitações separadas */

    solicitacao_cancelamento_id bigint null references solicitacao_dupla_concluida (id),
    /* id da solicitação que fez com que essa fosse cancelada,
       CASO tenha sido cancelada por isso (senão continua null)
       (pode ser cancelada quando atleta sai da competição também,
        ou quando ele/a não precisa mais de uma categoria,
        ou quando ele/a não precisa mais de um tipo de dupla) */
    /* cuidar para não armazenar o id da solicitacao_dupla_pendente */

    /* só um estado possível */
    check ((aceita_em is null and rejeitada_em is null and cancelada_em is not null)
        or (aceita_em is null and rejeitada_em is not null and cancelada_em is null)
        or (aceita_em is not null and rejeitada_em is null and cancelada_em is null))
);

--
create table if not exists dupla (
    id             serial primary key,
    competicao_id  bigint references competicao(id),
    categoria      text references categoria(descricao),
    atleta1_id     bigint references atleta(id),
    atleta2_id     bigint references atleta(id),
    solicitacao_id bigint references solicitacao_dupla_concluida(id),

    criado_em timestamp not null default now(),

    foreign key (atleta1_id, competicao_id)
    references atleta_competicao (atleta_id, competicao_id),

    foreign key (atleta2_id, competicao_id)
    references atleta_competicao (atleta_id, competicao_id)
);

--
CREATE TYPE tipo_notificacao AS ENUM (
    'atleta_incluido_na_competicao',
    'solicitacao_enviada',
    'solicitacao_recebida',
    'solicitacao_enviada_rejeitada',
    'solicitacao_recebida_rejeitada',
    'solicitacao_enviada_aceita',
    'solicitacao_recebida_aceita',
    'dupla_desfeita_pelo_outro',
    'dupla_desfeita_por_voce'
);

--
CREATE TABLE IF NOT EXISTS notificacao (
    /* decidi fazer numa tabela só em vez de notificacao_atleta, notificacao_solicitacao etc.
       porque nvdd com as tabelas separadas de solicitação ia tabelas demais, praticamente
       uma pra cada tipo de notificação (variando só enviado/recebido), então achei melhor
       deixar assim mesmo */

    id             serial primary key,
    tipo           tipo_notificacao not null,
    /* usuário que vai receber a notificação */
    tecnico_id     bigint references tecnico(id),
    /* ids que podem referenciar atletas, solicitações, duplas etc. */
    id_1           bigint not null,
    id_2           bigint not null,
    id_3           bigint not null,
    criado_em      timestamp not null default now(),
    visualizado_em timestamp null
);

--
/* registra os envios de e-mail que foram feitos por notificação
   fazer assim possibilita que tenha mais de um por notificação
   (caso a gente queira enviar um e-mail de novo se o técnico não visualizar por muito tempo) */
CREATE TABLE IF NOT EXISTS envio_email_notificacao (
    notificacao_id bigint references notificacao(id),
    enviado_em     timestamp not null default now()
);

--
/* o token em si (será jwt) descreve a ação */
CREATE TABLE IF NOT EXISTS token_acao (
    token                text primary key,
    criado_em            timestamp not null default now(),
    qtd_usos_permitidos  integer null,
    expira_em            timestamp null,
    check (qtd_usos_permitidos is null or qtd_usos_permitidos > 0),
    check (expira_em is null or expira_em > criado_em)
);

--
/* registra cada uso de um token */
CREATE TABLE IF NOT EXISTS uso_token_acao (
    token     text references token_acao(token),
    data_hora timestamp not null
);