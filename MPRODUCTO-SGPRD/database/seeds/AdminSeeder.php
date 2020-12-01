<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\limite_cre;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userAM = User::create([
            'nacionalidad'=>'V',
            'dni' => '12345678',
            'first_name' =>'Admin',
            'last_name' => 'Meritop',
            'image' => NULL,
            'email' => 'admin@meritop.com',
            'kind' => '1',
            'cod_tel' => '58412',
            'num_tel' => '9211211',
            'password' => bcrypt('123456'),
            'birthdate' => '1994-01-03',
            'deleted_at' => NULL,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $userAB = User::create([
            'nacionalidad'=>'V',
            'dni' => '12345678',
            'first_name' =>'Admin',
            'last_name' => 'Banco',
            'image' => NULL,
            'email' => 'admin@banplus.com',
            'kind' => '1',
            'cod_tel' => '58412',
            'num_tel' => '9211211',
            'password' => bcrypt('123456'),
            'birthdate' => '1994-01-03',
            'deleted_at' => NULL,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $userOP = User::create([
            'nacionalidad'=>'V',
            'dni' => '12345678',
            'first_name' =>'Operaciones',
            'last_name' => 'Banplus',
            'image' => NULL,
            'email' => 'operaciones@banplus.com',
            'kind' => '1',
            'cod_tel' => '58412',
            'num_tel' => '9211211',
            'password' => bcrypt('123456'),
            'birthdate' => '1994-01-03',
            'deleted_at' => NULL,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);

        $userCC = User::create([
            'nacionalidad'=>'V',
            'dni' => '12345678',
            'first_name' =>'CallCenter',
            'last_name' => 'Banplus',
            'image' => NULL,
            'email' => 'callcenter@banplus.com',
            'kind' => '1',
            'cod_tel' => '58412',
            'num_tel' => '9211211',
            'password' => bcrypt('123456'),
            'birthdate' => '1994-01-03',
            'deleted_at' => NULL,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);

        $comercioLE = User::create([
            'nacionalidad'=>'V',
            'dni' => '12345678',
            'first_name' =>'Comercio',
            'last_name' => 'La Esquina',
            'image' => NULL,
            'email' => 'comercio@laesquina.com',
            'kind' => '1',
            'cod_tel' => '58412',
            'num_tel' => '9211211',
            'password' => bcrypt('123456'),
            'birthdate' => '1994-01-03',
            'deleted_at' => NULL,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);

      /*  $comercioDT = User::create([
            'nacionalidad'=>'V',
            'dni' => '12345678',
            'first_name' =>'Comercio',
            'last_name' => "D'Tapas",
            'image' => NULL,
            'email' => 'comercio@dtapas.com',
            'kind' => '1',
            'cod_tel' => '58412',
            'num_tel' => '9211211',
            'password' => bcrypt('123456'),
            'birthdate' => '1994-01-03',
            'deleted_at' => NULL,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);*/

        $userSeg = User::create([
            'nacionalidad'=>'V',
            'dni' => '12345678',
            'first_name' =>'Seguridad',
            'last_name' => 'Banplus',
            'image' => NULL,
            'email' => 'seguridad@banplus.com',
            'kind' => '1',
            'cod_tel' => '58412',
            'num_tel' => '9211211',
            'password' => bcrypt('123456'),
            'birthdate' => '1994-01-03',
            'deleted_at' => NULL,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);


        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrador',
            'description' => 'Usuario administrador de la plataforma (Meritop C.A.)',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $banco = Role::create([
            'name' => 'admin_banco',
            'display_name' => 'Admin Banco',
            'description' => 'Usuario administrador de la entidad bancaria afiliada a la plataforma',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $comercio = Role::create([
            'name' => 'comercio',
            'display_name' => 'Comercio',
            'description' => 'Usuario del comercio afiliado a la plataforma',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $callcenter = Role::create([
            'name' => 'callcenter',
            'display_name' => 'Callcenter',
            'description' => 'Usuario Call Center de la entidad bancaria afiliada a la plataforma',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $miembro = Role::create([
            'name' => 'cliente',
            'display_name' => 'Cliente',
            'description' => 'Cliente VIP de la entidad bancaria afiliada a la plataforma',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $operacion = Role::create([
            'name' => 'operaciones',
            'display_name' => 'Operaciones',
            'description' => 'Usuario Operaciones de la entidad bancaria afiliada a la plataforma',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $seguridad = Role::create([
            'name' => 'seguridad',
            'display_name' => 'Seguridad',
            'description' => 'Usuario de Seguridad de la entidad bancaria afiliada a la plataforma',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

            $ul = Permission::create([
                'name' => 'user-list',
                'display_name' => 'Listar Usuario',
                'description' => 'Solo puede ver el Usuario'
            ]);
            $uc = Permission::create([
                'name' => 'user-create',
                'display_name' => 'Crear Usuario',
                'description' => 'Create Nuevo Usuario'
            ]);
            $ue = Permission::create([
                'name' => 'user-edit',
                'display_name' => 'Editar Usuario',
                'description' => 'Editar Usuario'
            ]);
            $ud = Permission::create([
                'name' => 'user-delete',
                'display_name' => 'Eliminar Usuario',
                'description' => 'Eliminar Usuario'
            ]);
            $iu = Permission::create([
                'name' => 'user-import',
                'display_name' => 'Importar Usuario',
                'description' => 'Importar Usuario'
            ]);
            $eu = Permission::create([
                'name' => 'user-export',
                'display_name' => 'Exportar Usuario',
                'description' => 'Exportar Usuario'
            ]);



            $rl = Permission::create([
                'name' => 'role-list',
                'display_name' => 'Listar Perfil',
                'description' => 'Solo puede ver el Perfil'
            ]);
            $rc = Permission::create([
                'name' => 'role-create',
                'display_name' => 'Crear Perfil',
                'description' => 'Create Nuevo Perfil'
            ]);
            $re = Permission::create([
                'name' => 'role-edit',
                'display_name' => 'Editar Perfil',
                'description' => 'Editar Perfil'
            ]);
            $rd = Permission::create([
                'name' => 'role-delete',
                'display_name' => 'Eliminar Perfil',
                'description' => 'Eliminar Perfil'
            ]);
            $ir = Permission::create([
                'name' => 'role-import',
                'display_name' => 'Importar Perfil',
                'description' => 'Importar Perfil'
            ]);
            $er = Permission::create([
                'name' => 'role-export',
                'display_name' => 'Exportar Perfil',
                'description' => 'Exportar Perfil'
            ]);



            $bl = Permission::create([
                'name' => 'banco-list',
                'display_name' => 'Listar Banco',
                'description' => 'Solo puede ver el Banco'
            ]);
            $bc = Permission::create([
                'name' => 'banco-create',
                'display_name' => 'Crear Banco',
                'description' => 'Create Nuevo Banco'
            ]);
            $be = Permission::create([
                'name' => 'banco-edit',
                'display_name' => 'Editar Banco',
                'description' => 'Editar Banco'
            ]);
            $bd = Permission::create([
                'name' => 'banco-delete',
                'display_name' => 'Eliminar Banco',
                'description' => 'Eliminar Banco'
            ]);
            $ib = Permission::create([
                'name' => 'banco-import',
                'display_name' => 'Importar Banco',
                'description' => 'Importar Banco'
            ]);
            $eb = Permission::create([
                'name' => 'banco-export',
                'display_name' => 'Exportar Banco',
                'description' => 'Exportar Banco'
            ]);




            $cl = Permission::create([
                'name' => 'comercio-list',
                'display_name' => 'Listar Comercios',
                'description' => 'Solo puede ver el Comercios'
            ]);
            $cc = Permission::create([
                'name' => 'comercio-create',
                'display_name' => 'Crear Comercios',
                'description' => 'Create Nuevo Comercios'
            ]);
            $ce = Permission::create([
                'name' => 'comercio-edit',
                'display_name' => 'Editar Comercios',
                'description' => 'Editar Comercios'
            ]);
            $cd = Permission::create([
                'name' => 'comercio-delete',
                'display_name' => 'Eliminar Comercios',
                'description' => 'Eliminar Comercios'
            ]);
            $ic = Permission::create([
                'name' => 'comercio-import',
                'display_name' => 'Importar Comercios',
                'description' => 'Importar Comercios'
            ]);
            $ec = Permission::create([
                'name' => 'comercio-export',
                'display_name' => 'Exportar Comercios',
                'description' => 'Exportar Comercios'
            ]);




            $tl = Permission::create([
                'name' => 'transacciones-list',
                'display_name' => 'Listar Transacciones',
                'description' => 'Solo puede ver el Transacciones'
            ]);
            $tc = Permission::create([
                'name' => 'transacciones-create',
                'display_name' => 'Crear Transacciones',
                'description' => 'Create Nuevo Transacciones'
            ]);
            $te = Permission::create([
                'name' => 'transacciones-edit',
                'display_name' => 'Editar Transacciones',
                'description' => 'Editar Transacciones'
            ]);
            $td = Permission::create([
                'name' => 'transacciones-delete',
                'display_name' => 'Eliminar Transacciones',
                'description' => 'Eliminar Transacciones'
            ]);
            $it = Permission::create([
                'name' => 'transacciones-import',
                'display_name' => 'Importar Transacciones',
                'description' => 'Importar Transacciones'
            ]);
            $et = Permission::create([
                'name' => 'transacciones-export',
                'display_name' => 'Exportar Transacciones',
                'description' => 'Exportar Transacciones'
            ]);



            $rpl = Permission::create([
                'name' => 'reportes-list',
                'display_name' => 'Listar Reportes',
                'description' => 'Solo puede ver el Reportes'
            ]);

            $rpc = Permission::create([
                'name' => 'reporte-consolidado',
                'display_name' => 'Reportes Consolidados',
                'description' => 'Ver Reportes Consolidados'
            ]);
            $rptc = Permission::create([
                'name' => 'totalizado-clientes',
                'display_name' => 'Reporte Totalizado Cliente',
                'description' => 'Totalizado Clientes'
            ]);
            $rptco = Permission::create([
                'name' => 'totalizado-comercio',
                'display_name' => 'Reporte Totalizado Comercio',
                'description' => 'Totalizado Comercios'
            ]);
            $rpdl = Permission::create([
                'name' => 'domiciliacion-liquidacion',
                'display_name' => 'Domiciliacion y Liquidacion',
                'description' => 'Domiciliacion y Liquidacion'
            ]);
            $erp = Permission::create([
                'name' => 'reportes-export',
                'display_name' => 'Exportar Reportes',
                'description' => 'Exportar Reportes'
            ]);


            $cll = Permission::create([
                'name' => 'carga-list',
                'display_name' => 'Listar Cargas',
                'description' => 'Puede ver la lista de Cargas'
            ]);
            $cmp = Permission::create([
                'name' => 'carga-pagos',
                'display_name' => 'Carga Pagos',
                'description' => 'Carga Masiva de Pagos'
            ]);
            $cmu = Permission::create([
                'name' => 'carga-usuarios',
                'display_name' => 'Carga Usuarios',
                'description' => 'Carga Masiva de Usuarios'
            ]);
            $cml = Permission::create([
                'name' => 'carga-limites',
                'display_name' => 'Carga Limites',
                'description' => 'Carga Masiva de Limites'
            ]);



        $userAM->attachRole($admin);
        $userAB->attachRole($banco);
        $userOP->attachRole($operacion);
        $userCC->attachRole($callcenter);
        /*$comercioLE->attachRole($comercio);
        $comercioDT->attachRole($comercio);*/
        $userSeg->attachRole($seguridad);

        //permisos para el administrador en usuarios
        $admin->attachPermission($ul);
        $admin->attachPermission($uc);
        $admin->attachPermission($ud);
        $admin->attachPermission($ue);
        $admin->attachPermission($iu);

        $admin->attachPermission($rl);
        $admin->attachPermission($rc);
        $admin->attachPermission($re);
        $admin->attachPermission($rd);

        //permisos para el administrador en comercio
        $admin->attachPermission($cl);
        $admin->attachPermission($cc);
        $admin->attachPermission($ce);

        //permisos para el administrador en banco
        $admin->attachPermission($bl);
        $admin->attachPermission($bc);
        $admin->attachPermission($be);

        //permisos para el administrador en transacciones
        $admin->attachPermission($tl);
        //$admin->attachPermission($et);

        //permisos para el administrador en reportes
        $admin->attachPermission($rpl);
        $admin->attachPermission($rpc);
        $admin->attachPermission($rptc);
        $admin->attachPermission($rptco);
        //$admin->attachPermission($erp);

        //permisos para el administradorBanco en usuarios
        $banco->attachPermission($ul);
        $banco->attachPermission($uc);
        $banco->attachPermission($ud);
        $banco->attachPermission($ue);
        $banco->attachPermission($iu);

        $banco->attachPermission($rl);
        $banco->attachPermission($rc);
        $banco->attachPermission($re);
        $banco->attachPermission($rd);

        //permisos para el administradorBanco en transacciones
        $banco->attachPermission($tl);

        //permisos para el administradorBanco en comercio
        $banco->attachPermission($cl);
        $banco->attachPermission($cc);
        $banco->attachPermission($ce);

        //permisos para el administradorBanco en reportes
        $banco->attachPermission($rpl);
        $banco->attachPermission($rpc);
        $banco->attachPermission($rptc);
        $banco->attachPermission($rptco);
        $banco->attachPermission($rpdl);

        //permisos para el administradorBanco en carga masiva
        $banco->attachPermission($cll);
        $banco->attachPermission($cmp);
        $banco->attachPermission($cmu);
        $banco->attachPermission($cml);

        //permisos para el comercio en transacciones
        $comercio->attachPermission($tl);
        $comercio->attachPermission($tc);
        $comercio->attachPermission($te);

         //permisos para el comercio en reportes
        $comercio->attachPermission($rpl);
        $comercio->attachPermission($rpc);
        $comercio->attachPermission($rptc);
        $comercio->attachPermission($erp);

        //permisos para operaciones en comercio
        $operacion->attachPermission($cl);
        $operacion->attachPermission($cc);
        $operacion->attachPermission($ce);

        //permisos para operaciones en reportes
        $operacion->attachPermission($rpl);
        $operacion->attachPermission($rpc);
        $operacion->attachPermission($rptc);
        $operacion->attachPermission($rptco);
        $operacion->attachPermission($erp);

        //permisos para operaciones en transacciones
        $operacion->attachPermission($tl);
        $operacion->attachPermission($et);

        //permisos para operaciones en usuarios
        $operacion->attachPermission($ul);
        $operacion->attachPermission($uc);
        $operacion->attachPermission($ud);
        $operacion->attachPermission($ue);
        $operacion->attachPermission($iu);

        //permisos para operaciones en carga masiva
        $operacion->attachPermission($cll);
        $operacion->attachPermission($cmu);

        //permisos para callcenter en transacciones
        $callcenter->attachPermission($tl);
        $callcenter->attachPermission($tc);
        $callcenter->attachPermission($te);
        $callcenter->attachPermission($td);

        //permisos para callcenter en reportes
        $callcenter->attachPermission($rpl);
        $callcenter->attachPermission($rpc);
        $callcenter->attachPermission($rptc);
        $callcenter->attachPermission($rptco);
        $callcenter->attachPermission($erp);

        $limit1 = limite_cre::create([
            'descripcion' => 'Ilimitado',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $limit2 = limite_cre::create([
            'descripcion' => 'Limitado',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $limit3 = limite_cre::create([
            'descripcion' => 'No Aplica',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

    }


}
