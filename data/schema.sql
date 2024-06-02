create table if not exists promocode
(
    code       varchar(10) not null
        primary key,
    device_id  varchar(36) null,
    ip_long    bigint      null,
    applied_at timestamp   null,
    constraint promocode_device_id_index
        unique (device_id)
);

create index promocode_ip_long_index
    on promocode (ip_long);

