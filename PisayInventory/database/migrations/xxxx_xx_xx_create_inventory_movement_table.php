use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryMovementTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_movement', function (Blueprint $table) {
            $table->id('MovementID');
            $table->unsignedBigInteger('ItemID');
            $table->enum('MovementType', ['IN', 'OUT']);
            $table->decimal('Quantity', 10, 2);
            $table->string('ReferenceNumber');
            $table->string('ReferenceType');
            $table->unsignedBigInteger('ReferenceID');
            $table->text('Notes')->nullable();
            $table->dateTime('DateCreated');
            $table->unsignedBigInteger('CreatedByID');
            $table->dateTime('DateModified')->nullable();
            $table->unsignedBigInteger('ModifiedByID')->nullable();
            $table->boolean('IsDeleted')->default(false);
            $table->dateTime('DateDeleted')->nullable();
            $table->unsignedBigInteger('DeletedByID')->nullable();
            
            $table->foreign('ItemID')->references('ItemID')->on('items');
            $table->foreign('CreatedByID')->references('EmployeeID')->on('employee');
            $table->foreign('ModifiedByID')->references('EmployeeID')->on('employee');
            $table->foreign('DeletedByID')->references('EmployeeID')->on('employee');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_movement');
    }
} 