
create table issue_person (
    id_issue        int not null references issue(id),
    id_person       int not null references person(id),
    primary key(id_issue, id_person)
);
-- to find the issues of a person
create index issue_person_id_issue_idx on issue_person(id_issue);
-- to find the persons related to an issue
create index issue_person_id_person_idx on issue_person(id_person);
