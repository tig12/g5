
-- Used to store members of a group
create table person_groop (
    id_person       int not null references person(id),
    id_groop        int not null references groop(id),
    primary key(id_person, id_groop)
);
-- to find the members of a group
create index person_groop_id_groop_idx on person_groop(id_groop);
-- to find the groups to which a person belongs
create index person_groop_id_person_idx on person_groop(id_person);
