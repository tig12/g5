
-- 

create table wikiproject_person (
    id_project      int not null references wikiproject(id),
    id_person       int not null references person(id),
    primary key(id_project, id_person)
);
CREATE INDEX wikiproject_person_project_idx ON wikiproject_person(id_project);
CREATE INDEX wikiproject_person_person_idx ON wikiproject_person(id_person);
