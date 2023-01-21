
-- Contains the last additions to the wiki

create table wikirecent (
    id_person       int not null references person(id),
    dateadd         timestamp not null
);
CREATE INDEX wikirecent_date_idx ON wikirecent(dateadd);
