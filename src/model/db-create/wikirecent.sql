
-- Contains the last additions to the wiki

create table wikirecent (
    id_person       int not null references person(id),
    dateadd         timestamp not null,
    description     text
);
CREATE INDEX wikirecent_person_idx ON wikirecent(id_person);
CREATE INDEX wikirecent_date_idx ON wikirecent(dateadd);
