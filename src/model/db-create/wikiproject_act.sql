
-- 

create table wikiproject_act (
    id_project      int not null references wikiproject(id),
    id_person       int not null references person(id),
    primary key(id_project, id_person)
);
CREATE INDEX wikiproject_act_project_idx ON wikiproject_act(id_project);
CREATE INDEX wikiproject_act_person_idx ON wikiproject_act(id_person);
