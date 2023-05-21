ALTER TABLE categoria RENAME COLUMN idade_limite TO idade_menor_que;

--
ALTER TABLE categoria ADD COLUMN idade_maior_que INTEGER;
/* para poder participar na categoria:
   precisa ter idade maior que `idade_maior_que` em algum dia do ano
   precisa ter idade menor que `idade_menor_que` em todos os dias do ano (se fizer 17 anos no ano, não pode mais participar da sub17) */

--
INSERT INTO categoria
(descricao, idade_maior_que, idade_menor_que)
VALUES
('Sub 9',       null,    9),
('Sub 11',      null,   11),
('Sub 13',      null,   13),
('Sub 15',      null,   15),
('Sub 17',      null,   17),
('Sub 19',      null,   19),
('Sub 21',      null,   21),
('Aberta',      null, null),
('Sênior',        35, null),
('Veterano I',    45, null),
('Veterano II',   55, null);
/* https://www.badminton.org.br/admin/upload/documentos/4a93d9ec7f.pdf */