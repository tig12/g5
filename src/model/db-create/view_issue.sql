
-- View used by postgrest api

create or replace view view_issue as
    select
        i.slug          as issue_slug,
        i.type          as issue_type,
        i.description   as issue_description,
        p.slug          as person_slug,
        p.name          as person_name,
        p.birth         as person_birth,
        p.partial_ids   as person_partial_ids,
        p.occus         as person_occus
    from person "p",
         issue_person "ip",
         issue "i"
    where p.id = ip.id_person
      and i.id = ip.id_issue
    order by p.slug;
