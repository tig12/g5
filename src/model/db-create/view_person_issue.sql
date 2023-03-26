
-- View used by postgrest api

create or replace view view_person_issue as
select
    i.id            "issue_id",
    i.slug          "issue_slug",
    i.type          "issue_type",
    i.description   "issue_description",
    p.id            "person_id"
from
    issue i,
    person p,
    issue_person
where p.id = issue_person.id_person
  and i.id = issue_person.id_issue
;
